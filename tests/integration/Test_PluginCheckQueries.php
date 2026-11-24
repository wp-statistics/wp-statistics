<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\Admin\FilterHandler\FilterManager;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\DB;
use WP_STATISTICS\User;
use WP_STATISTICS\Visitor;

/**
 * Queries moved to $wpdb->prepare() with %i table names (Plugin Check), and the
 * pre-6.7 fallback for current_user_can_for_site(): same results as before.
 */
class Test_PluginCheckQueries extends WP_UnitTestCase
{
    private static $tables = ['visitor', 'pages', 'visitor_relationships'];

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
    }

    public function tearDown(): void
    {
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

    private function addVisitor($ip, $userId = 0, $hits = 1)
    {
        global $wpdb;

        $day = DateTime::get();

        $wpdb->insert(DB::table('visitor'), [
            'last_counter'   => $day,
            'referred'       => '',
            'agent'          => 'Chrome',
            'platform'       => 'Windows',
            'version'        => '1',
            'ip'             => $ip,
            'user_id'        => $userId,
            'hits'           => $hits,
            'location'       => 'DE',
            'source_channel' => 'direct',
            'first_view'     => "{$day} 08:00:00",
            'last_view'      => "{$day} 08:00:00",
        ]);

        return $wpdb->insert_id;
    }

    public function test_exist_ip_in_day_finds_row_and_selected_columns()
    {
        $id = $this->addVisitor('192.0.2.10');

        $row = Visitor::exist_ip_in_day('192.0.2.10', DateTime::get(), ['ID', 'hits']);
        $this->assertEquals($id, $row->ID);
        $this->assertObjectNotHasProperty('agent', $row);

        $this->assertFalse(Visitor::exist_ip_in_day('192.0.2.99'));
    }

    public function test_find_today_row_matches_unique_key()
    {
        $id = $this->addVisitor('192.0.2.11');

        $method = new ReflectionMethod(Visitor::class, 'find_today_row');
        $method->setAccessible(true);

        $row = $method->invoke(null, [
            'last_counter' => DateTime::get(),
            'ip'           => '192.0.2.11',
            'agent'        => 'Chrome',
            'platform'     => 'Windows',
            'version'      => '1',
        ]);

        $this->assertEquals($id, $row->ID);
    }

    public function test_relationships_insert_then_update_same_day()
    {
        global $wpdb;

        $visitorId = $this->addVisitor('192.0.2.12');

        Visitor::save_visitors_relationships(5, $visitorId);
        Visitor::save_visitors_relationships(5, $visitorId);

        $count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM %i WHERE visitor_id = %d', DB::table('visitor_relationships'), $visitorId));
        $this->assertSame('1', (string)$count);
    }

    public function test_get_top_and_count()
    {
        $this->addVisitor('192.0.2.13', 0, 3);
        $this->addVisitor('192.0.2.14', 0, 7);

        $top = Visitor::getTop(['day' => 'today']);
        $this->assertCount(2, $top);

        $this->assertSame('2', (string)Visitor::Count());
        $this->assertSame('1', (string)Visitor::Count(['key' => 'hits', 'compare' => '>', 'value' => 5]));
    }

    public function test_users_visitor_and_filter_user_search()
    {
        $userId = self::factory()->user->create(['user_login' => 'pcheckuser', 'user_email' => 'pcheck@example.test']);
        $other  = self::factory()->user->create(['user_login' => 'someoneelse', 'user_email' => 'other@example.test']);
        $this->addVisitor('192.0.2.15', $userId);
        $this->addVisitor('192.0.2.16', $other);

        $users = Visitor::get_users_visitor();
        $this->assertArrayHasKey($userId, $users);
        $this->assertArrayHasKey($other, $users);

        $manager = new FilterManager();
        $all     = $manager->getUser('');
        $this->assertCount(2, $all);

        $found = $manager->getUser('pcheck');
        $this->assertCount(1, $found);
        $this->assertSame((string)$userId, (string)$found[0]['id']);
    }

    public function test_capability_check_for_current_site()
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $this->assertTrue(User::checkUserCapability('manage_options'));

        wp_set_current_user(self::factory()->user->create(['role' => 'subscriber']));
        $this->assertNull(User::checkUserCapability('manage_options'));

        $method = new ReflectionMethod(User::class, 'currentUserCanForBlog');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke(null, get_current_blog_id(), 'read'));
        $this->assertFalse($method->invoke(null, get_current_blog_id(), 'manage_options'));
    }

    public function test_standalone_visitor_card_tag()
    {
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-assets.php';

        $tag  = wp_get_script_tag(['src' => 'https://example.test/visitor-card.min.js?ver=1']);
        $html = Admin_Assets::insertBeforeBodyEnd('<html><body></body></html>', $tag);

        $this->assertMatchesRegularExpression('#<script[^>]* src="https://example\.test/visitor-card\.min\.js\?ver=1"[^>]*></script>\s*</body>#', $html);
    }
}
