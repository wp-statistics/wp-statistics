<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Network;

/**
 * The network admin menu and the site list helper must not call multisite-only
 * functions (get_sites()) on a single site.
 *
 * @see https://github.com/wp-statistics/wp-statistics/issues/1161
 */
class Test_NetworkMenu extends WP_UnitTestCase
{
    private $network;

    public function setUp(): void
    {
        parent::setUp();

        if (!class_exists(Network::class)) {
            // The file creates its own instance when it is loaded.
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-network.php';
        }

        $this->network = new Network();
    }

    public function tearDown(): void
    {
        remove_action('network_admin_menu', [$this->network, 'wp_admin_menu']);
        parent::tearDown();
    }

    public function test_network_menu_is_not_registered_on_single_site()
    {
        if (is_multisite()) {
            $this->markTestSkipped('Single-site only.');
        }

        $this->assertFalse(
            has_action('network_admin_menu', [$this->network, 'wp_admin_menu']),
            'The network admin menu must not be registered on a single site.'
        );
    }

    public function test_network_admin_menu_action_does_not_fatal_on_single_site()
    {
        if (is_multisite()) {
            $this->markTestSkipped('Single-site only.');
        }

        // This is what wp-admin/network/menu.php fires before
        // wp-admin/network/admin.php stops with "Multisite support is not enabled."
        do_action('network_admin_menu', '');

        $this->assertFalse(function_exists('get_sites'), 'get_sites() is not loaded on a single site.');
    }

    public function test_sites_list_returns_current_site_on_single_site()
    {
        if (is_multisite()) {
            $this->markTestSkipped('Single-site only.');
        }

        $this->assertSame([get_current_blog_id()], Helper::get_wp_sites_list());
    }

    public function test_network_menu_is_registered_on_multisite()
    {
        if (!is_multisite()) {
            $this->markTestSkipped('Multisite only.');
        }

        $this->assertSame(10, has_action('network_admin_menu', [$this->network, 'wp_admin_menu']));
    }

    public function test_sites_list_returns_every_site_on_multisite()
    {
        if (!is_multisite()) {
            $this->markTestSkipped('Multisite only.');
        }

        $siteId = self::factory()->blog->create();
        $sites  = array_map('intval', Helper::get_wp_sites_list());

        $this->assertContains(get_current_blog_id(), $sites);
        $this->assertContains((int)$siteId, $sites);
    }
}
