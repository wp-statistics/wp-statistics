<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * Online-now marker on the visitor pill: VisitorDecorator::isOnline(), primeOnlineStatus()
 * and the dot in views/components/visitor-information.php.
 */
class Test_VisitorOnlineStatus extends WP_UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        foreach (['visitor', 'pages'] as $table) {
            TableHandler::createTable($table, Manager::getSchemaForTable($table));
        }
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->emptyTables();
    }

    public function tearDown(): void
    {
        remove_all_filters('wp_statistics_online_visitors_timeframe');
        $this->emptyTables();
        parent::tearDown();
    }

    private function emptyTables()
    {
        global $wpdb;

        foreach (['visitor', 'pages'] as $table) {
            $wpdb->query('DELETE FROM `' . DB::table($table) . '`');
        }
    }

    private function visitor(array $row)
    {
        return new VisitorDecorator((object)array_merge([
            'ID'           => 7,
            'ip'           => '203.0.113.9',
            'location'     => 'DE',
            'region'       => 'Bavaria',
            'city'         => 'Munich',
            'agent'        => 'Chrome',
            'version'      => '150.0.1',
            'platform'     => 'Windows',
            'device'       => 'desktop',
            'model'        => '',
            'user_id'      => 0,
            'hits'         => 1,
            'last_counter' => DateTime::get(),
        ], $row));
    }

    public function test_online_window_boundaries()
    {
        $today     = DateTime::get();
        $timeframe = ['from' => $today . ' 10:00:00', 'to' => $today . ' 10:05:00'];

        $this->assertTrue($this->visitor(['last_view' => $today . ' 10:00:00'])->isOnline($timeframe), 'start of the window counts');
        $this->assertTrue($this->visitor(['last_view' => $today . ' 10:05:00'])->isOnline($timeframe), 'end of the window counts');
        $this->assertFalse($this->visitor(['last_view' => $today . ' 09:59:59'])->isOnline($timeframe), 'one second before the window');
        $this->assertFalse($this->visitor(['last_view' => $today . ' 10:05:01'])->isOnline($timeframe), 'after the window');
        $this->assertFalse($this->visitor(['last_view' => null])->isOnline($timeframe), 'no last view');
    }

    public function test_row_from_another_day_is_not_online()
    {
        $yesterday = DateTime::get('-1 day');
        $timeframe = ['from' => $yesterday . ' 23:58:00', 'to' => DateTime::get() . ' 00:03:00'];

        // Same rule as OnlineModel: last_counter must be today.
        $this->assertFalse($this->visitor(['last_counter' => $yesterday, 'last_view' => $yesterday . ' 23:59:00'])->isOnline($timeframe));
    }

    public function test_default_window_follows_the_online_timeframe_filter()
    {
        $now        = DateTime::get('now', 'Y-m-d H:i:s');
        $sixMinutes = DateTime::get('-6 min', 'Y-m-d H:i:s');

        $this->assertTrue($this->visitor(['last_view' => $now])->isOnline());
        $this->assertFalse($this->visitor(['last_view' => $sixMinutes])->isOnline(), 'outside the default 5 minutes');

        add_filter('wp_statistics_online_visitors_timeframe', function () {
            return 10;
        });

        $this->assertTrue($this->visitor(['last_view' => $sixMinutes])->isOnline());
        $this->assertSame(DateTime::get('-10 min', 'Y-m-d H:i:s'), (new OnlineModel())->getTimeframe()['from']);
    }

    public function test_rows_without_last_view_are_offline_until_primed_with_one_query()
    {
        global $wpdb;

        $now = DateTime::get('now', 'Y-m-d H:i:s');
        foreach ([101 => $now, 102 => DateTime::get('-1 hour', 'Y-m-d H:i:s')] as $id => $lastView) {
            $wpdb->insert(DB::table('visitor'), [
                'ID'           => $id,
                'last_counter' => DateTime::get(),
                'referred'     => '',
                'agent'        => 'Chrome',
                'platform'     => 'Windows',
                'version'      => (string)$id,
                'ip'           => '198.51.100.' . $id,
                'user_id'      => 0,
                'hits'         => 1,
                'last_view'    => $lastView,
            ]);
        }

        // A list query without last_view (as an add-on might run).
        $online  = new VisitorDecorator((object)['ID' => 101, 'last_counter' => DateTime::get()]);
        $offline = new VisitorDecorator((object)['ID' => 102, 'last_counter' => DateTime::get()]);
        $hasCols = $this->visitor(['ID' => 103, 'last_view' => $now]);

        $this->assertFalse($online->isOnline(), 'no query per row: unknown rows count as offline');

        $before = $wpdb->num_queries;
        VisitorDecorator::primeOnlineStatus([$online, $offline, $hasCols]);
        $this->assertSame(1, $wpdb->num_queries - $before, 'one query for the whole list');

        $this->assertTrue($online->isOnline());
        $this->assertFalse($offline->isOnline());
        $this->assertTrue($hasCols->isOnline());

        $before = $wpdb->num_queries;
        VisitorDecorator::primeOnlineStatus([$online, $offline, $hasCols]);
        $this->assertSame(0, $wpdb->num_queries - $before, 'already primed');
    }

    public function test_pill_shows_dot_and_label_only_when_online()
    {
        $html = View::load('components/visitor-information', ['visitor' => $this->visitor(['last_view' => DateTime::get('now', 'Y-m-d H:i:s')])], true);

        $this->assertStringContainsString('wps-visitor-info--online', $html);
        $this->assertStringContainsString('<span class="wps-visitor-info__online"><span class="screen-reader-text">Online now</span></span>', $html);
        $this->assertMatchesRegularExpression('/wps-visitor-info__slot--status[^>]*title="Online now"/', $html);
        $this->assertMatchesRegularExpression('/aria-label="Visitor details: [^"]*Online now/', $html);

        $html = View::load('components/visitor-information', ['visitor' => $this->visitor(['last_view' => DateTime::get('-1 hour', 'Y-m-d H:i:s')])], true);

        $this->assertStringNotContainsString('wps-visitor-info__online', $html);
        $this->assertStringNotContainsString('Online now', $html);
    }

    public function test_label_names_the_current_page_escaped()
    {
        global $wpdb;

        $postId = self::factory()->post->create(['post_title' => 'Soup <b>&</b> "bread"', 'post_status' => 'publish']);
        $wpdb->insert(DB::table('pages'), ['id' => $postId, 'uri' => '/soup/', 'type' => 'post', 'count' => 1, 'date' => DateTime::get()]);
        $pageId = $wpdb->insert_id;

        $visitor = $this->visitor(['last_view' => DateTime::get('now', 'Y-m-d H:i:s'), 'last_page' => $pageId]);
        $label   = $visitor->getOnlineLabel();
        $html    = View::load('components/visitor-information', ['visitor' => $visitor], true);

        $this->assertStringStartsWith('Online now · on Soup', $label);
        $this->assertStringNotContainsString('<b>', $label, 'tags stripped');
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('"bread"', $html, 'quotes escaped in the title attribute');
    }
}
