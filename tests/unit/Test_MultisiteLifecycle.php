<?php

use WP_Statistics\Core\Operations\Activator;
use WP_Statistics\Core\Operations\Loader;
use WP_STATISTICS\DB;
use WP_STATISTICS\Network;
use WP_STATISTICS\Option;
use WP_Statistics\Utils\Query;

/**
 * Multisite lifecycle: network activation, new sites, deleted sites, global tables
 * in queries and the network overview page.
 */
class Test_MultisiteLifecycle extends WP_UnitTestCase
{
    public function tearDown(): void
    {
        if (is_multisite()) {
            $plugins = (array)get_site_option('active_sitewide_plugins', []);
            unset($plugins[plugin_basename(WP_STATISTICS_MAIN_FILE)]);
            update_site_option('active_sitewide_plugins', $plugins);
        }

        parent::tearDown();
    }

    private function requireMultisite()
    {
        if (!is_multisite()) {
            $this->markTestSkipped('Multisite only.');
        }
    }

    /**
     * Works for real and temporary tables (the test suite creates temporary ones,
     * which SHOW TABLES does not list).
     */
    private function tableExists($table)
    {
        global $wpdb;

        $suppress = $wpdb->suppress_errors(true);
        $result   = $wpdb->query("SELECT 1 FROM `{$table}` LIMIT 1"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->suppress_errors($suppress);

        return $result !== false;
    }

    private function networkActivate()
    {
        $plugins = (array)get_site_option('active_sitewide_plugins', []);
        $plugins[plugin_basename(WP_STATISTICS_MAIN_FILE)] = time();
        update_site_option('active_sitewide_plugins', $plugins);
    }

    // (a) Network activation --------------------------------------------------

    public function test_network_activation_sets_up_every_site()
    {
        $this->requireMultisite();

        $siteId = self::factory()->blog->create();

        switch_to_blog($siteId);
        delete_option('wp_statistics_plugin_version');
        delete_option(Option::$opt_name);
        restore_current_blog();

        new Activator(true);

        switch_to_blog($siteId);
        $version = get_option('wp_statistics_plugin_version');
        $options = get_option(Option::$opt_name);
        $jobs    = get_option('wp_statistics_jobs');
        $visitor = DB::table('visitor');
        restore_current_blog();

        $this->assertSame(WP_STATISTICS_VERSION, $version, 'each site stores the plugin version, so no migration notice shows');
        $this->assertIsArray($options, 'each site gets the default options');
        $this->assertNotEmpty($options);
        $this->assertIsArray($jobs, 'background processes are marked as initiated on each site');
        $this->assertNotEmpty($jobs);
        $this->assertStringContainsString("_{$siteId}_statistics_visitor", $visitor);
        $this->assertTrue($this->tableExists($visitor), 'the site has its own tables');
    }

    // (b) New sites -----------------------------------------------------------

    public function test_site_hooks_are_registered_outside_wp_admin()
    {
        $this->assertFalse(is_admin());

        $loader = null;
        foreach (['wp_initialize_site', 'wpmu_drop_tables'] as $hook) {
            global $wp_filter;
            $found = false;

            if (isset($wp_filter[$hook])) {
                foreach ($wp_filter[$hook]->callbacks as $callbacks) {
                    foreach ($callbacks as $callback) {
                        if (is_array($callback['function']) && $callback['function'][0] instanceof Loader) {
                            $found = true;
                        }
                    }
                }
            }

            $this->assertTrue($found, "{$hook} is hooked on every request");
        }

        $this->assertFalse(has_action('wpmu_new_blog', [new Loader(), 'addTableOnCreateBlog']), 'the deprecated wpmu_new_blog hook is not used');
    }

    public function test_new_site_gets_tables_and_version_when_network_active()
    {
        $this->requireMultisite();
        $this->networkActivate();

        $siteId = self::factory()->blog->create();

        switch_to_blog($siteId);
        $version = get_option('wp_statistics_plugin_version');
        $options = get_option(Option::$opt_name);
        $visitor = DB::table('visitor');
        restore_current_blog();

        $this->assertSame(WP_STATISTICS_VERSION, $version);
        $this->assertIsArray($options);
        $this->assertTrue($this->tableExists($visitor), 'a site created by any path (here wp_insert_site) gets tables');
    }

    public function test_new_site_is_left_alone_when_not_network_active()
    {
        $this->requireMultisite();

        $siteId = self::factory()->blog->create();

        switch_to_blog($siteId);
        $version = get_option('wp_statistics_plugin_version');
        restore_current_blog();

        $this->assertFalse($version);
    }

    // (c) Deleted sites -------------------------------------------------------

    public function test_drop_tables_filter_returns_a_flat_list_with_the_site_tables()
    {
        global $wpdb;

        $tables = ['posts' => $wpdb->prefix . 'posts', 'options' => $wpdb->prefix . 'options'];
        $result = (new Loader())->removeTableOnDeleteBlog($tables, get_current_blog_id());

        foreach ($result as $table) {
            $this->assertIsString($table, 'no nested array (was $tables[] = array_merge(...))');
        }

        $this->assertContains($wpdb->prefix . 'posts', $result);
        $this->assertContains($wpdb->prefix . 'statistics_visitor', $result);
        $this->assertContains($wpdb->prefix . 'statistics_summary_totals', $result);
        $this->assertSame(count($result), count(array_unique($result)));
    }

    public function test_legacy_install_method_gives_the_same_tables_as_the_loader()
    {
        global $wpdb;

        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';

        $tables = [$wpdb->prefix . 'posts'];
        $legacy = (new \WP_STATISTICS\Install())->remove_table_on_delete_blog($tables);

        $this->assertSame((new Loader())->removeTableOnDeleteBlog($tables, get_current_blog_id()), $legacy);
        $this->assertFalse(has_filter('wpmu_drop_tables', [\WP_STATISTICS\Install::class, 'remove_table_on_delete_blog']));
    }

    public function test_deleting_a_site_drops_its_tables()
    {
        $this->requireMultisite();
        $this->networkActivate();

        $siteId = self::factory()->blog->create();

        switch_to_blog($siteId);
        $visitor = DB::table('visitor');
        $drop    = apply_filters('wpmu_drop_tables', [], $siteId);
        restore_current_blog();

        $this->assertTrue($this->tableExists($visitor));
        $this->assertContains($visitor, $drop, 'WordPress gets the site table names while switched to that site');

        wp_delete_site($siteId);

        $this->assertFalse($this->tableExists($visitor), 'site tables are dropped with the site');
    }

    // (d) Global tables keep their alias on sub-sites --------------------------

    public function test_remove_table_prefix_keeps_global_table_names_on_sub_sites()
    {
        global $wpdb;

        $query = Query::select('*');

        $this->assertSame('visitor', $query->removeTablePrefix($wpdb->prefix . 'statistics_visitor'));
        $this->assertSame('users', $query->removeTablePrefix($wpdb->users));
        $this->assertSame('usermeta', $query->removeTablePrefix($wpdb->usermeta));
        $this->assertSame('posts', $query->removeTablePrefix($wpdb->posts));

        if (!is_multisite()) {
            return;
        }

        $siteId = self::factory()->blog->create();
        switch_to_blog($siteId);

        $query = Query::select('*');
        $this->assertNotSame($wpdb->base_prefix, $wpdb->prefix);
        $this->assertSame('visitor', $query->removeTablePrefix($wpdb->prefix . 'statistics_visitor'));
        $this->assertSame('posts', $query->removeTablePrefix($wpdb->posts));
        $this->assertSame('users', $query->removeTablePrefix($wpdb->users), 'wp_users must not lose characters on wp_2_');
        $this->assertSame('usermeta', $query->removeTablePrefix($wpdb->usermeta));

        $sql = Query::select('users.ID')->from('users')->getQuery();
        $this->assertStringContainsString("{$wpdb->users} AS users", $sql);

        restore_current_blog();
    }

    // (f) Deactivation and uninstall cleanup ---------------------------------

    public function test_network_deactivation_clears_the_health_check_on_every_site()
    {
        $this->requireMultisite();

        $siteId = self::factory()->blog->create();

        foreach ([get_current_blog_id(), $siteId] as $id) {
            switch_to_blog($id);
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'wp_statistics_tracking_health_hook');
            restore_current_blog();
        }

        \WP_Statistics::deactivator(true);

        foreach ([get_current_blog_id(), $siteId] as $id) {
            switch_to_blog($id);
            $next = wp_next_scheduled('wp_statistics_tracking_health_hook');
            restore_current_blog();

            $this->assertFalse($next, "site {$id} has no health check left");
        }
    }

    public function test_uninstall_cleanup_removes_install_flags_and_cron_hooks()
    {
        update_option('wp_statistics_is_fresh', true);
        update_option('wp_statistics_queue_background_process', ['completed' => true]);

        foreach (['wp_statistics_referrals_db_hook', 'wp_statistics_licenses_hook', 'wp_statistics_record_daily_summary'] as $hook) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', $hook);
        }

        $uninstaller = (new ReflectionClass(\WP_Statistics\Core\Operations\Uninstaller::class))->newInstanceWithoutConstructor();
        $wpdb        = new ReflectionProperty(\WP_Statistics\Core\AbstractCore::class, 'wpdb');
        $wpdb->setAccessible(true);
        $wpdb->setValue($uninstaller, $GLOBALS['wpdb']);

        $uninstaller->cleanupSiteData();

        $this->assertFalse(get_option('wp_statistics_is_fresh'));
        $this->assertFalse(get_option('wp_statistics_queue_background_process'));

        foreach (['wp_statistics_referrals_db_hook', 'wp_statistics_licenses_hook', 'wp_statistics_record_daily_summary'] as $hook) {
            $this->assertFalse(wp_next_scheduled($hook), "{$hook} is cleared");
        }
    }

    // (e) Network overview page -----------------------------------------------

    public function test_network_overview_links_use_current_pages_without_double_slash()
    {
        $this->requireMultisite();

        if (!class_exists(Network::class)) {
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-network.php';
        }

        $this->networkActivate();
        $siteId = self::factory()->blog->create();
        $sites  = Network::getSitesOverview();
        $ids    = wp_list_pluck($sites, 'id');

        $this->assertContains((int)$siteId, $ids);

        $legacy = ['wps_hits_page', 'wps_online_page', 'wps_referrers_page', 'wps_searches_page', 'wps_taxonomies_page', 'wps_browser_page', 'wps_platform_page', 'wps_top-visitors_page'];

        foreach ($sites as $site) {
            $this->assertTrue($site['active']);
            $this->assertNotEmpty($site['links']);

            foreach ($site['links'] as $link) {
                $this->assertDoesNotMatchRegularExpression('#[^:]//#', $link['url'], 'no double slash');
                foreach ($legacy as $slug) {
                    $this->assertStringNotContainsString("page={$slug}", $link['url']);
                }
            }
        }

        $html = get_echo([new Network(), 'overview']);
        $this->assertStringContainsString('wps-network-site', $html);
        $this->assertStringContainsString('page=wps_overview_page', $html);
        $this->assertStringContainsString('page=wps_referrals_page', $html);
        $this->assertStringNotContainsString('wps_hits_page', $html);
    }
}
