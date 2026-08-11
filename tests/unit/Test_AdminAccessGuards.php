<?php

use WP_Statistics\Service\Admin\Metabox\MetaboxManager;
use WP_STATISTICS\Admin_Assets;
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
        wp_set_current_user(0);
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
        $this->setCurrentUserWithRole('administrator');

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
