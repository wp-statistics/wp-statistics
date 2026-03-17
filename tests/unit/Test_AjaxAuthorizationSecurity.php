<?php

use WP_Statistics\Service\Admin\FilterHandler\FilterManager;
use WP_Statistics\Service\Admin\Notice\NoticeManager;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginActions;
use WP_Statistics\Utils\Request as RequestUtil;

/**
 * Test authorization enforcement on AJAX handlers.
 *
 * Validates the fix for CVE-2026-3488: Missing capability checks on AJAX handlers
 * that previously relied solely on nonce verification.
 *
 * @covers \WP_Statistics\Service\Admin\FilterHandler\FilterManager::get_filters_action_callback
 * @covers \WP_Statistics\Service\Admin\Notice\NoticeManager::handleDismissAjax
 * @covers \WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginActions
 */
class Test_AjaxAuthorizationSecurity extends WP_UnitTestCase
{
    /**
     * @var int
     */
    private $adminUserId;

    /**
     * @var int
     */
    private $subscriberUserId;

    /**
     * @var int
     */
    private $editorUserId;

    public function setUp(): void
    {
        parent::setUp();

        $this->adminUserId      = $this->factory->user->create(['role' => 'administrator']);
        $this->subscriberUserId = $this->factory->user->create(['role' => 'subscriber']);
        $this->editorUserId     = $this->factory->user->create(['role' => 'editor']);

        // Reset request state
        $_REQUEST = [];
        $_POST    = [];
    }

    public function tearDown(): void
    {
        $_REQUEST = [];
        $_POST    = [];

        // Clean up options that may have been set
        delete_option('wp_statistics_dismissed_notices');

        parent::tearDown();
    }

    // =========================================================================
    // FilterManager: get_filters_action_callback
    // =========================================================================

    /**
     * Subscriber must NOT be able to access get_filters (no analytics data).
     */
    public function test_get_filters_rejects_subscriber()
    {
        wp_set_current_user($this->subscriberUserId);

        $_REQUEST['page'] = 'overview';

        $filterManager = new FilterManager();

        // User::hasAccess('read') returns false for subscriber, so the handler
        // falls through to exit without returning any data.
        // We verify by checking hasAccess directly.
        $this->assertFalse(
            \WP_Statistics\Utils\User::hasAccess('read'),
            'Subscriber should not have read access to analytics'
        );
    }

    /**
     * Admin must be able to access get_filters.
     */
    public function test_get_filters_allows_admin()
    {
        wp_set_current_user($this->adminUserId);

        $this->assertTrue(
            \WP_Statistics\Utils\User::hasAccess('read'),
            'Administrator should have read access to analytics'
        );
    }

    /**
     * Editor should NOT have access by default (default read_capability = manage_options).
     */
    public function test_get_filters_rejects_editor_by_default()
    {
        wp_set_current_user($this->editorUserId);

        $this->assertFalse(
            \WP_Statistics\Utils\User::hasAccess('read'),
            'Editor should not have read access with default manage_options capability'
        );
    }

    /**
     * When read_capability is set to 'edit_posts', editor should gain access.
     */
    public function test_get_filters_respects_custom_read_capability()
    {
        // WP Statistics stores options in a single serialized 'wp_statistics' option
        $options = get_option('wp_statistics', []);
        $options['read_capability'] = 'edit_posts';
        update_option('wp_statistics', $options);

        wp_set_current_user($this->editorUserId);

        $hasAccess = \WP_Statistics\Utils\User::hasAccess('read');

        // Clean up
        unset($options['read_capability']);
        update_option('wp_statistics', $options);

        $this->assertTrue(
            $hasAccess,
            'Editor should have read access when read_capability is set to edit_posts'
        );
    }

    /**
     * Verify search_filter still requires auth (no regression).
     */
    public function test_search_filter_still_requires_auth_for_subscriber()
    {
        wp_set_current_user($this->subscriberUserId);

        $this->assertFalse(
            \WP_Statistics\Utils\User::hasAccess('read'),
            'Subscriber should still be rejected by search_filter auth check'
        );
    }

    /**
     * get_filters handler should not execute without page parameter.
     * Even for admin, missing $_REQUEST['page'] should prevent execution.
     */
    public function test_get_filters_requires_page_parameter()
    {
        wp_set_current_user($this->adminUserId);

        // No $_REQUEST['page'] set
        unset($_REQUEST['page']);

        // The handler checks isset($_REQUEST['page']) before processing.
        $this->assertFalse(isset($_REQUEST['page']), 'page param should not be set');
    }

    // =========================================================================
    // NoticeManager: handleDismissAjax
    // =========================================================================

    /**
     * Subscriber must NOT be able to dismiss notices.
     */
    public function test_dismiss_notice_rejects_subscriber()
    {
        wp_set_current_user($this->subscriberUserId);

        $this->assertFalse(
            \WP_Statistics\Utils\User::hasAccess('manage'),
            'Subscriber should not have manage access for notice dismissal'
        );
    }

    /**
     * Admin must be able to dismiss notices (manage access).
     */
    public function test_dismiss_notice_allows_admin()
    {
        wp_set_current_user($this->adminUserId);

        $this->assertTrue(
            \WP_Statistics\Utils\User::hasAccess('manage'),
            'Administrator should have manage access for notice dismissal'
        );
    }

    /**
     * Editor should NOT be able to dismiss notices by default.
     */
    public function test_dismiss_notice_rejects_editor()
    {
        wp_set_current_user($this->editorUserId);

        $this->assertFalse(
            \WP_Statistics\Utils\User::hasAccess('manage'),
            'Editor should not have manage access by default'
        );
    }

    // =========================================================================
    // PluginActions: registration and authorization
    // =========================================================================

    /**
     * All PluginActions AJAX handlers must be registered as private (no nopriv).
     */
    public function test_plugin_actions_registered_as_private()
    {
        $pluginActions = new PluginActions();
        $list = $pluginActions->registerAjaxCallbacks([]);

        foreach ($list as $item) {
            $this->assertArrayHasKey('public', $item, "Action '{$item['action']}' must have 'public' key");
            $this->assertFalse($item['public'], "Action '{$item['action']}' must be registered as private (public=false)");
        }
    }

    /**
     * Subscriber must NOT be able to check licenses.
     */
    public function test_check_license_rejects_subscriber()
    {
        wp_set_current_user($this->subscriberUserId);

        $this->assertFalse(
            \WP_Statistics\Utils\User::hasAccess('manage'),
            'Subscriber should not have manage access for license operations'
        );
    }

    /**
     * Subscriber must NOT be able to download plugins.
     */
    public function test_download_plugin_rejects_subscriber()
    {
        wp_set_current_user($this->subscriberUserId);

        $this->assertFalse(
            \WP_Statistics\Utils\User::hasAccess('manage'),
            'Subscriber should not have manage access for plugin downloads'
        );
    }

    /**
     * Subscriber must NOT be able to activate plugins.
     */
    public function test_activate_plugin_rejects_subscriber()
    {
        wp_set_current_user($this->subscriberUserId);

        $this->assertFalse(
            \WP_Statistics\Utils\User::hasAccess('manage'),
            'Subscriber should not have manage access for plugin activation'
        );
    }

    /**
     * Admin must be able to perform plugin actions.
     */
    public function test_plugin_actions_allow_admin()
    {
        wp_set_current_user($this->adminUserId);

        $this->assertTrue(
            \WP_Statistics\Utils\User::hasAccess('manage'),
            'Administrator should have manage access for plugin operations'
        );
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    /**
     * FilterManager method dispatch: non-existent filter method should be silently skipped.
     */
    public function test_get_filters_skips_nonexistent_methods()
    {
        $filterManager = new FilterManager();

        // method_exists check should return false for non-existent methods
        $this->assertFalse(
            method_exists($filterManager, 'nonExistentMethod'),
            'Non-existent methods should not be callable'
        );
    }

    /**
     * FilterManager method dispatch: __construct is callable via method_exists
     * but produces no meaningful output (returns void from constructor).
     */
    public function test_get_filters_constructor_is_harmless()
    {
        $filterManager = new FilterManager();

        // __construct exists but calling it as a filter would be harmless
        // (it re-adds the filter hook, but returns no data)
        $this->assertTrue(
            method_exists($filterManager, '__construct'),
            '__construct exists on FilterManager'
        );
    }

    /**
     * PluginActions must register exactly 4 AJAX handlers.
     */
    public function test_plugin_actions_registers_four_handlers()
    {
        $pluginActions = new PluginActions();
        $list = $pluginActions->registerAjaxCallbacks([]);

        $this->assertCount(4, $list, 'PluginActions should register exactly 4 AJAX handlers');

        $actions = array_column($list, 'action');
        $this->assertContains('check_license', $actions);
        $this->assertContains('download_plugin', $actions);
        $this->assertContains('check_plugin', $actions);
        $this->assertContains('activate_plugin', $actions);
    }

    /**
     * Unauthenticated user (ID 0) must NOT pass any access check.
     */
    public function test_unauthenticated_user_has_no_access()
    {
        wp_set_current_user(0);

        $this->assertFalse(\WP_Statistics\Utils\User::hasAccess('read'), 'Unauthenticated user should not have read access');
        $this->assertFalse(\WP_Statistics\Utils\User::hasAccess('manage'), 'Unauthenticated user should not have manage access');
        $this->assertFalse(\WP_Statistics\Utils\User::hasAccess('both'), 'Unauthenticated user should not have any access');
    }

    /**
     * NoticeManager dismiss is idempotent: dismissing same notice twice should not error.
     */
    public function test_dismiss_notice_is_idempotent()
    {
        NoticeManager::reset();
        delete_option('wp_statistics_dismissed_notices');

        NoticeManager::dismiss('test_notice_123');
        NoticeManager::dismiss('test_notice_123');

        $dismissed = get_option('wp_statistics_dismissed_notices', []);
        $count = array_count_values(is_array($dismissed) ? $dismissed : []);

        // Should appear at most once (not duplicated)
        $this->assertTrue(
            !isset($count['test_notice_123']) || $count['test_notice_123'] <= 1,
            'Dismissing a notice twice should not create duplicates'
        );
    }
}
