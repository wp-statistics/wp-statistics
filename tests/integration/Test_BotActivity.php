<?php

namespace WP_Statistics\Tests;

use WP_STATISTICS\DB;
use WP_STATISTICS\Exclusion;
use WP_STATISTICS\Option;
use WP_Statistics\Models\BotActivityModel;
use WP_Statistics\Service\Analytics\BotActivity;
use WP_Statistics\Service\Analytics\DeviceDetection\UserAgentService;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_UnitTestCase;

class Test_BotActivity extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        BotActivity::ensureTable();
        Option::update('bot_activity', false);
        Option::update('record_exclusions', false);
        Option::update('store_ua', false);

        global $wpdb;
        $wpdb->query('TRUNCATE TABLE `' . DB::table('bot_activity') . '`');
    }

    public function tearDown(): void
    {
        Option::update('bot_activity', false);
        Option::update('store_ua', false);
        parent::tearDown();
    }

    public function test_detects_an_existing_table_when_a_like_pattern_also_matches()
    {
        global $wpdb;

        $table          = DB::table('bot_activity');
        $lookalikeTable = str_replace('_', '0', $table);

        $wpdb->query("CREATE TABLE `{$lookalikeTable}` (`ID` bigint(20) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`ID`))");

        try {
            $this->assertTrue(BotActivity::ensureTable());
        } finally {
            $wpdb->query("DROP TABLE IF EXISTS `{$lookalikeTable}`");
        }
    }

    public function test_records_only_enabled_bot_exclusions_and_updates_recent_activity()
    {
        global $wpdb;

        $profile = $this->createVisitorProfile();
        $table   = DB::table('bot_activity');

        Exclusion::record(['exclusion_reason' => 'robot'], $profile);
        $this->assertSame('0', $wpdb->get_var("SELECT COUNT(*) FROM `{$table}`"));

        Option::update('bot_activity', true);
        Option::update('store_ua', true);

        Exclusion::record(['exclusion_reason' => 'excluded url'], $profile);
        $this->assertSame('0', $wpdb->get_var("SELECT COUNT(*) FROM `{$table}`"));

        Exclusion::record(['exclusion_reason' => 'robot'], $profile);

        $activity = $wpdb->get_row("SELECT * FROM `{$table}`");

        $this->assertNotNull($activity);
        $this->assertSame('robot', $activity->reason);
        $this->assertSame('203.0.113.8', $activity->ip);
        $this->assertSame('ExampleBot/1.0', $activity->user_agent);
        $this->assertSame('/products', $activity->uri);
        $this->assertSame('1', $activity->hits);

        Exclusion::record(['exclusion_reason' => 'robot'], $profile);

        $this->assertSame('1', $wpdb->get_var("SELECT COUNT(*) FROM `{$table}`"));
        $this->assertSame('2', $wpdb->get_var("SELECT hits FROM `{$table}`"));

        $model      = new BotActivityModel();
        $activities = $model->getActivities();

        $this->assertSame(1, $model->countActivities());
        $this->assertCount(1, $activities);
        $this->assertSame('robot', $activities[0]->reason);
        $this->assertSame(1, $model->countActivities(['ip' => '203.0.113.8', 'reason' => 'robot']));
        $this->assertSame(0, $model->countActivities(['reason' => 'headless']));
    }

    public function test_respects_user_agent_storage_setting()
    {
        global $wpdb;

        Option::update('bot_activity', true);

        $profile = $this->createVisitorProfile();
        $table   = DB::table('bot_activity');

        Exclusion::record(['exclusion_reason' => 'robot'], $profile);
        $this->assertSame('', $wpdb->get_row("SELECT * FROM `{$table}`")->user_agent);

        Option::update('store_ua', true);
        Exclusion::record(['exclusion_reason' => 'robot'], $profile);

        $activities = $wpdb->get_results("SELECT * FROM `{$table}` ORDER BY ID");
        $this->assertSame('', $activities[0]->user_agent);
        $this->assertSame('ExampleBot/1.0', $activities[1]->user_agent);
    }

    public function test_includes_recent_activity_at_the_upper_time_boundary()
    {
        global $wpdb;

        Option::update('bot_activity', true);
        Exclusion::record(['exclusion_reason' => 'robot'], $this->createVisitorProfile());

        $table = DB::table('bot_activity');
        $wpdb->update(
            $table,
            ['last_view' => time() + 1],
            ['ID' => $wpdb->get_var("SELECT ID FROM `{$table}`")]
        );

        $model = new BotActivityModel();

        $this->assertSame(1, $model->countActivities());
        $this->assertCount(1, $model->getActivities());
    }

    private function createVisitorProfile()
    {
        $userAgent = $this->createMock(UserAgentService::class);
        $userAgent->method('getDeviceDetector')->willReturn(null);

        $profile = $this->getMockBuilder(VisitorProfile::class)
            ->onlyMethods([
                'getHttpUserAgent',
                'getProcessedIPForStorage',
                'getRequestUri',
                'getUserAgent',
            ])
            ->getMock();

        $profile->method('getHttpUserAgent')->willReturn('ExampleBot/1.0');
        $profile->method('getProcessedIPForStorage')->willReturn('203.0.113.8');
        $profile->method('getRequestUri')->willReturn('/products?token=secret');
        $profile->method('getUserAgent')->willReturn($userAgent);

        return $profile;
    }
}
