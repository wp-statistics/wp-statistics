<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\Admin\Metabox\MetaboxDataProvider;
use WP_Statistics\Service\Admin\Metabox\MetaboxHelper;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\Visitors;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\DB;

/**
 * Visitors card: Latest, Most active and Online now in one card, one shared table.
 */
class Test_VisitorsCard extends WP_UnitTestCase
{
    private static $tables = ['visitor', 'pages', 'visitor_relationships'];

    private $userId;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        foreach (self::$tables as $table) {
            TableHandler::createTable($table, Manager::getSchemaForTable($table));
        }
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->emptyTables();

        $this->userId = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($this->userId);
    }

    public function tearDown(): void
    {
        unset($_REQUEST['segment'], $_GET['segment']);
        remove_all_filters('wp_statistics_active_user_online');
        $GLOBALS['current_screen'] = null;
        $this->emptyTables();
        parent::tearDown();
    }

    private function emptyTables()
    {
        global $wpdb;

        foreach (self::$tables as $table) {
            $wpdb->query('DELETE FROM `' . DB::table($table) . '`');
        }
    }

    private function addVisitor($data = [])
    {
        global $wpdb;

        static $n = 0;
        $n++;

        $day = DateTime::get();

        $wpdb->insert(DB::table('visitor'), array_merge([
            'last_counter'   => $day,
            'referred'       => '',
            'agent'          => 'Chrome',
            'platform'       => 'Windows',
            'version'        => '1',
            'ip'             => '192.0.2.' . ($n % 250),
            'user_id'        => 0,
            'hits'           => 1,
            'location'       => 'DE',
            'source_channel' => 'direct',
            'first_view'     => "{$day} 01:00:00",
            'last_view'      => "{$day} 01:00:00",
        ], $data));

        return $wpdb->insert_id;
    }

    private function card($segment = null)
    {
        if ($segment !== null) {
            $_REQUEST['segment'] = $segment;
        } else {
            unset($_REQUEST['segment']);
        }

        return new Visitors();
    }

    private function onlineOff()
    {
        add_filter('wp_statistics_active_user_online', '__return_false');
    }

    public function test_card_replaces_the_three_widgets()
    {
        $this->assertContains(Visitors::class, MetaboxHelper::$metaboxes);
        $this->assertSame('wp-statistics-visitors-widget', (new Visitors())->getKey());
        $this->assertSame('Visitors', (new Visitors())->getName());

        foreach (['LatestVisitor', 'MostActiveVisitors', 'CurrentlyOnline'] as $old) {
            $this->assertFalse(class_exists('WP_Statistics\\Service\\Admin\\Metabox\\Metaboxes\\' . $old), $old);
        }
    }

    public function test_online_segment_only_while_online_is_on()
    {
        $this->assertSame(['latest', 'active', 'online'], Visitors::getAvailableSegments(true));
        $this->assertSame(['latest', 'active'], Visitors::getAvailableSegments(false));

        $this->onlineOff();
        $this->assertSame(['latest', 'active'], Visitors::getAvailableSegments());
    }

    public function test_resolve_segment()
    {
        $all = ['latest', 'active', 'online'];

        $this->assertSame('active', Visitors::resolveSegment('active', 'online', $all));
        $this->assertSame('online', Visitors::resolveSegment(null, 'online', $all));
        $this->assertSame('online', Visitors::resolveSegment('bogus', 'online', $all));
        $this->assertSame('latest', Visitors::resolveSegment(null, '', $all));

        // Online off: an Online choice falls back to Latest.
        $this->assertSame('latest', Visitors::resolveSegment('online', 'online', ['latest', 'active']));
        $this->assertSame('active', Visitors::resolveSegment('online', 'active', ['latest', 'active']));
    }

    public function test_default_is_latest_by_last_view()
    {
        $this->addVisitor(['hits' => 9, 'last_view' => DateTime::get() . ' 01:00:00']);
        $this->addVisitor(['hits' => 1, 'last_view' => DateTime::get() . ' 02:00:00']);

        $card = $this->card();
        $html = $card->getData();

        $this->assertSame('latest', $card->getSegment());
        $this->assertStringContainsString('wps-visitor-table--sort-last', $html);
        $this->assertStringContainsString('role="tabpanel"', $html);
        $this->assertStringContainsString('data-segment="latest"', $html);
        $this->assertFalse($card->getOptions()['datepicker']);
        $this->assertStringContainsString('tab=visitors', $card->getOptions()['button']);
    }

    public function test_most_active_sorts_by_views_with_a_date_range()
    {
        $low  = $this->addVisitor(['hits' => 2]);
        $high = $this->addVisitor(['hits' => 12]);

        $card    = $this->card('active');
        $html    = $card->getData();
        $options = $card->getOptions();

        $this->assertStringContainsString('wps-visitor-table--sort-views', $html);
        $this->assertLessThan(strpos($html, "visitor_id={$low}"), strpos($html, "visitor_id={$high}"));
        $this->assertTrue($options['datepicker']);
        $this->assertStringContainsString('tab=top-visitors', $options['button']);
        $this->assertSame('active', $options['segment']);
    }

    public function test_online_lists_who_is_on_now_and_counts_them()
    {
        $now = DateTime::get('now', 'Y-m-d H:i:s');
        $this->addVisitor(['last_view' => $now]);
        $this->addVisitor(['last_view' => $now]);
        $this->addVisitor(['last_view' => DateTime::get() . ' 00:00:01']);

        $provider = new MetaboxDataProvider();
        $this->assertSame(2, $provider->getOnlineVisitorsCount());

        $card    = $this->card('online');
        $html    = $card->getData();
        $options = $card->getOptions();

        $this->assertSame(2, substr_count($html, '<tr>') - 1); // minus the header row
        $this->assertSame(2, $options['online']);
        $this->assertStringContainsString('tab=online', $options['button']);
        $this->assertFalse($options['datepicker']);
    }

    public function test_nobody_online_shows_the_empty_message_and_zero()
    {
        $card    = $this->card('online');
        $html    = $card->getData();
        $options = $card->getOptions();

        $this->assertStringContainsString('No one online right now', $html);
        $this->assertSame(0, $options['online']);
        $this->assertSame('online', $options['segment']);
    }

    public function test_online_off_hides_the_segment_and_falls_back_to_latest()
    {
        $this->onlineOff();

        $card    = $this->card('online');
        $options = $card->getOptions();

        $this->assertSame('latest', $options['segment']);
        $this->assertNull($options['online']);
        $this->assertSame(['latest', 'active'], wp_list_pluck($options['segments'], 'key'));
    }

    public function test_segment_is_remembered_per_user_and_screen()
    {
        $this->card('active')->getSegment();
        $this->assertSame('active', Visitors::getSavedSegment($this->userId, 'overview'));

        // Next load without a choice opens the saved one.
        $this->assertSame('active', $this->card()->getSegment());

        // A bad value is not saved.
        $this->card('bogus')->getSegment();
        $this->assertSame('active', Visitors::getSavedSegment($this->userId, 'overview'));

        Visitors::saveSegment($this->userId, 'dashboard', 'online');
        $this->assertSame(['overview' => 'active', 'dashboard' => 'online'], get_user_meta($this->userId, Visitors::SEGMENT_META_KEY, true));
    }

    public function test_switch_labels_and_order()
    {
        $options = $this->card()->getOptions();

        $this->assertSame([
            ['key' => 'latest', 'label' => 'Latest'],
            ['key' => 'active', 'label' => 'Most active'],
            ['key' => 'online', 'label' => 'Online now'],
        ], $options['segments']);
    }

    public function test_dashboard_lists_five_rows_and_follows_the_segment()
    {
        set_current_screen('dashboard');

        for ($i = 0; $i < 7; $i++) {
            $this->addVisitor();
        }

        $card = $this->card('latest');
        $html = $card->getData();

        $this->assertTrue(MetaboxHelper::isDashboardContext());
        $this->assertStringContainsString('wps-dash-list--latest', $html);
        $this->assertSame(5, substr_count($html, '<tr>') - 1);
        $this->assertStringContainsString('View all visitors', $card->getOptions()['button']);
        $this->assertSame('latest', Visitors::getSavedSegment($this->userId, 'dashboard'));

        $empty = $this->card('online')->getData();
        $this->assertStringContainsString('wps-dash-empty', $empty);
        $this->assertStringContainsString('No one online right now', $empty);
    }

    public function test_footer_links_per_segment()
    {
        $this->assertStringContainsString('tab=visitors', Visitors::getFooterLink('latest', false)['link']);
        $this->assertSame('View Most Active Visitors', Visitors::getFooterLink('active', false)['title']);
        $this->assertSame('View Online Visitors', Visitors::getFooterLink('online', false)['title']);
        $this->assertSame('View all online visitors', Visitors::getFooterLink('online', true)['title']);
    }
}
