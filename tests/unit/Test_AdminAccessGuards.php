<?php

use WP_Statistics\Service\Admin\Metabox\MetaboxManager;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_STATISTICS\User;

class Test_AdminAccessGuards extends WP_UnitTestCase
{
    private $assets;
    private $metaboxManager;
    private $metaboxListCalls = 0;
    private $originalOptions;
    private $hadOriginalOptions;

    public function setUp(): void
    {
        parent::setUp();

        if (!class_exists(Admin_Assets::class)) {
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-assets.php';
        }

        $this->originalOptions    = get_option(Option::$opt_name);
        $this->hadOriginalOptions = false !== $this->originalOptions;
        $this->assets             = new Admin_Assets();
        $this->metaboxManager     = new MetaboxManager();

        add_filter('wp_statistics_metabox_list', [$this, 'recordMetaboxList']);
        set_current_screen('dashboard');
        $this->resetAssetQueues();
    }

    public function tearDown(): void
    {
        global $pagenow;

        remove_filter('wp_statistics_admin_menu_list', [$this, 'restrictContentAnalyticsToCustomCap'], 20);
        remove_filter('wp_statistics_metabox_list', [$this, 'recordMetaboxList']);
        remove_action('admin_init', [$this->metaboxManager, 'registerMetaboxes']);
        remove_action('admin_init', [$this->metaboxManager, 'hideDashboardMetaboxes']);
        remove_action('admin_enqueue_scripts', [$this->assets, 'admin_styles'], 999);
        remove_action('admin_enqueue_scripts', [$this->assets, 'admin_scripts'], 999);
        remove_filter('wp_statistics_enqueue_chartjs', [$this->assets, 'shouldEnqueueChartJs']);

        if ($this->hadOriginalOptions) {
            update_option(Option::$opt_name, $this->originalOptions);
        } else {
            delete_option(Option::$opt_name);
        }

        remove_role('wps_manager_only');
        remove_role('wps_reader_only');
        remove_role('wps_content_analyst');
        wp_set_current_user(0);
        unset($_REQUEST['page']);
        $pagenow = 'index.php';
        set_current_screen('front');

        parent::tearDown();
    }

    public function recordMetaboxList(array $metaboxes): array
    {
        $this->metaboxListCalls++;

        return [];
    }

    public function test_unauthorized_users_do_not_build_metaboxes()
    {
        $this->setCurrentUserWithRole('subscriber');

        $this->metaboxManager->registerMetaboxes();
        $this->metaboxManager->hideDashboardMetaboxes();

        $this->assertSame(0, $this->metaboxListCalls);
    }

    public function test_authorized_readers_build_metaboxes()
    {
        $this->setCurrentUserWithRole('administrator');

        $this->metaboxManager->registerMetaboxes();
        $this->metaboxManager->hideDashboardMetaboxes();

        $this->assertSame(2, $this->metaboxListCalls);
    }

    public function test_unauthorized_users_do_not_receive_admin_assets()
    {
        $this->setCurrentUserWithRole('subscriber');

        $this->assets->admin_styles();
        $this->assets->admin_scripts('index.php');

        $this->assertFalse(wp_style_is(Admin_Assets::$prefix, 'enqueued'));
        $this->assertFalse(wp_script_is(Admin_Assets::$prefix, 'enqueued'));
    }

    public function test_authorized_readers_receive_admin_assets()
    {
        add_role(
            'wps_reader_only',
            'WP Statistics Reader',
            [
                'read'           => true,
                'wps_read_stats' => true,
            ]
        );

        $options                      = Option::getOptions();
        $options['manage_capability'] = 'wps_manage_stats';
        $options['read_capability']   = 'wps_read_stats';
        Option::save_options($options);

        $this->setCurrentUserWithRole('wps_reader_only');

        $this->assertTrue(User::Access('read'));
        $this->assertFalse(User::Access('manage'));

        $this->assets->admin_styles();
        $this->assets->admin_scripts('index.php');

        $this->assertTrue(wp_style_is(Admin_Assets::$prefix, 'enqueued'));
        $this->assertTrue(wp_script_is(Admin_Assets::$prefix, 'enqueued'));
    }

    public function test_manage_only_users_keep_admin_assets_but_skip_metaboxes()
    {
        add_role(
            'wps_manager_only',
            'WP Statistics Manager',
            [
                'read'             => true,
                'wps_manage_stats' => true,
            ]
        );

        $options                      = Option::getOptions();
        $options['manage_capability'] = 'wps_manage_stats';
        $options['read_capability']   = 'wps_read_stats';
        Option::save_options($options);

        $this->setCurrentUserWithRole('wps_manager_only');

        $this->assertTrue(User::Access('manage'));
        $this->assertFalse(User::Access('read'));

        $this->metaboxManager->registerMetaboxes();
        $this->assertSame(0, $this->metaboxListCalls);

        $this->assets->admin_styles();
        $this->assets->admin_scripts('index.php');

        $this->assertTrue(wp_style_is(Admin_Assets::$prefix, 'enqueued'));
        $this->assertTrue(wp_script_is(Admin_Assets::$prefix, 'enqueued'));
        $this->assertSame(0, $this->metaboxListCalls);
    }

    public function test_custom_role_admitted_to_a_plugin_page_receives_admin_assets()
    {
        global $pagenow;

        add_role(
            'wps_content_analyst',
            'WP Statistics Content Analyst',
            [
                'read'                       => true,
                'wps_view_content_analytics' => true,
            ]
        );

        $options                      = Option::getOptions();
        $options['manage_capability'] = 'wps_manage_stats';
        $options['read_capability']   = 'wps_read_stats';
        Option::save_options($options);

        add_filter('wp_statistics_admin_menu_list', [$this, 'restrictContentAnalyticsToCustomCap'], 20);
        $this->setCurrentUserWithRole('wps_content_analyst');

        $this->assertFalse(User::Access());

        $pagenow          = 'admin.php';
        $_REQUEST['page'] = Menus::get_page_slug('content-analytics');

        $this->assertTrue(User::hasPageAccess());

        $this->assets->admin_styles();
        $this->assets->admin_scripts('statistics_page_wps_content-analytics_page');

        $this->assertTrue(wp_style_is(Admin_Assets::$prefix, 'enqueued'));
        $this->assertTrue(wp_style_is(Admin_Assets::$prefix . '-daterangepicker', 'enqueued'));
        $this->assertTrue(wp_script_is(Admin_Assets::$prefix, 'enqueued'));
        $this->assertTrue(wp_script_is(Admin_Assets::$prefix . '-daterangepicker', 'enqueued'));

        $localizedData = wp_scripts()->get_data(Admin_Assets::$prefix, 'data');
        $this->assertIsString($localizedData);
        $this->assertStringContainsString('"user_date_range"', $localizedData);
        $this->assertStringContainsString('"from":', $localizedData);
        $this->assertStringContainsString('"to":', $localizedData);
    }

    public function test_users_without_the_page_capability_receive_no_assets_on_a_plugin_page_slug()
    {
        global $pagenow;

        $options                      = Option::getOptions();
        $options['manage_capability'] = 'wps_manage_stats';
        $options['read_capability']   = 'wps_read_stats';
        Option::save_options($options);

        add_filter('wp_statistics_admin_menu_list', [$this, 'restrictContentAnalyticsToCustomCap'], 20);
        $this->setCurrentUserWithRole('subscriber');

        $pagenow          = 'admin.php';
        $_REQUEST['page'] = Menus::get_page_slug('content-analytics');

        $this->assertFalse(User::hasPageAccess());

        $this->assets->admin_styles();
        $this->assets->admin_scripts('statistics_page_wps_content-analytics_page');

        $this->assertFalse(wp_style_is(Admin_Assets::$prefix, 'enqueued'));
        $this->assertFalse(wp_script_is(Admin_Assets::$prefix, 'enqueued'));
    }

    public function test_capability_granted_on_the_user_instead_of_the_role_is_honoured()
    {
        $options                      = Option::getOptions();
        $options['manage_capability'] = 'wps_manage_stats';
        $options['read_capability']   = 'wps_read_stats';
        Option::save_options($options);

        $userId = self::factory()->user->create(['role' => 'subscriber']);
        $user   = get_user_by('id', $userId);
        $user->add_cap('wps_read_stats');
        wp_set_current_user($userId);

        // No role carries the capability, so the resolver still degrades to manage_options.
        $this->assertSame('manage_options', User::ExistCapability('wps_read_stats'));

        $this->assertTrue(User::Access('read'));
        $this->assertFalse(User::Access('manage'));

        $this->assets->admin_styles();
        $this->assets->admin_scripts('index.php');

        $this->assertTrue(wp_style_is(Admin_Assets::$prefix, 'enqueued'));
        $this->assertTrue(wp_script_is(Admin_Assets::$prefix, 'enqueued'));
    }

    /**
     * Mimics an add-on that hands a single analytics page its own capability,
     * which is how Roles and Permissions admits a custom role to one page.
     */
    public function restrictContentAnalyticsToCustomCap(array $menus): array
    {
        $existing = isset($menus['content_analytics']) ? $menus['content_analytics'] : [];

        $menus['content_analytics'] = array_merge($existing, [
            'sub'      => 'overview',
            'title'    => 'Content Analytics',
            'page_url' => 'content-analytics',
            'cap'      => 'wps_view_content_analytics',
        ]);

        return $menus;
    }

    private function setCurrentUserWithRole(string $role): void
    {
        $userId = self::factory()->user->create(['role' => $role]);
        wp_set_current_user($userId);
    }

    private function resetAssetQueues(): void
    {
        wp_styles()->queue  = [];
        wp_scripts()->queue = [];
    }
}
