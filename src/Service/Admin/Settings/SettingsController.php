<?php

namespace WP_Statistics\Service\Admin\Settings;

use WP_Statistics\Components\Option;
use WP_Statistics\Utils\User;

/**
 * Settings page controller.
 *
 * Handles the rendering of the Settings page which mounts the React app.
 * The React app handles navigation via TanStack Router with hash-based routing.
 *
 * @since 15.0.0
 */
class SettingsController
{
    /**
     * Singleton instance.
     *
     * @var SettingsController|null
     */
    private static $instance = null;

    /**
     * Page slug.
     *
     * @var string
     */
    const PAGE_SLUG = 'wps_settings_page';

    /**
     * Get singleton instance.
     *
     * @return SettingsController
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Hooks can be added here if needed
    }

    /**
     * Render the Settings page.
     *
     * This renders the same React mount point as the dashboard.
     * The React app detects the route from the URL hash (#/settings, #/settings/general, etc.)
     * and renders the appropriate settings tab.
     *
     * @return void
     */
    public function view()
    {
        // The view file renders the React mount point
        include WP_STATISTICS_DIR . 'views/pages/settings/index.php';
    }

    /**
     * Check if current user can access settings.
     *
     * @return bool
     */
    public static function canAccess()
    {
        $manageCap = User::getExistingCapability(Option::getValue('manage_capability', 'manage_options'));
        return current_user_can($manageCap);
    }

    /**
     * Get the admin page URL for settings.
     *
     * @param string $tab Optional tab to navigate to (e.g., 'general', 'privacy', 'notifications')
     * @return string
     */
    public static function getPageUrl($tab = '')
    {
        $url = admin_url('admin.php?page=' . self::PAGE_SLUG);

        if (!empty($tab)) {
            $url .= '#/settings/' . sanitize_key($tab);
        } else {
            $url .= '#/settings';
        }

        return $url;
    }

    /**
     * Check if we're on the settings page.
     *
     * @return bool
     */
    public static function isSettingsPage()
    {
        global $pagenow;
        return is_admin()
            && $pagenow === 'admin.php'
            && isset($_GET['page'])
            && $_GET['page'] === self::PAGE_SLUG;
    }
}
