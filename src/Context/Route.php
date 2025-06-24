<?php

namespace WP_Statistics\Context;

use WP_STATISTICS\Option;
use WP_STATISTICS\User;

/**
 * WordPress route‑detection helper.
 *
 * Provides static methods that determine which core WordPress
 * screen or endpoint the current HTTP request is targeting.
 * Currently exposes {@see Route::isLoginPage()} but can be
 * extended with additional route checks (REST, admin‑ajax, etc.).
 *
 * @package WP_Statistics\Context
 * @since   15.0.0
 */
final class Route
{
    /**
     * Returns true when the current request is for the core WordPress
     * login or registration screen (wp-login.php / wp-register.php).
     *
     * @return bool
     */
    public static function isLoginPage()
    {
        if (isset($GLOBALS['pagenow']) &&
            in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php'], true)) {
            return true;
        }

        if (defined('WP_CLI') && WP_CLI) {
            return false;
        }

        if (empty($_SERVER['HTTP_HOST']) || empty($_SERVER['SCRIPT_NAME'])) {
            return false;
        }

        $scheme     = is_ssl() ? 'https' : 'http';
        $host       = sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']));
        $script     = sanitize_text_field(wp_unslash($_SERVER['SCRIPT_NAME']));
        $currentUrl = $scheme . '://' . $host . $script;

        $currentPath = wp_parse_url($currentUrl, PHP_URL_PATH);
        $loginPath   = wp_parse_url(wp_login_url(), PHP_URL_PATH);

        return $currentPath === $loginPath;
    }

    /**
     * Check if the current screen is using the block editor (Gutenberg).
     *
     * @return bool True if the block editor is active on the current screen.
     */
    public static function isBlockEditorScreen()
    {
        $current_screen = get_current_screen();
        return (
            (method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor()) ||
            (function_exists('is_gutenberg_page')) && is_gutenberg_page()
        );
    }

    /**
     * Get the current screen ID.
     *
     * @return string The screen ID if available, otherwise an empty string.
     */
    public static function getScreenId()
    {
        $screen = get_current_screen();

        if (empty($screen->id)) {
            return '';
        }

        return $screen->id;
    }

    /**
     * Check if the current screen ID matches any in the provided list.
     *
     * @param array $screenIds List of allowed screen IDs.
     * @return bool True if current screen ID is in the list, false otherwise.
     */
    public static function isScreen(array $screenIds)
    {
        $screen = self::getScreenId();

        if (empty($screen)) {
            return false;
        }

        return in_array($screen, $screenIds, true);
    }

    /**
     * Determine whether the admin bar should be shown for the current user and context.
     *
     * Checks if the plugin's admin bar option is enabled, the WordPress admin bar is allowed,
     * and the current user has access. Allows overriding via the 'wp_statistics_show_admin_bar' filter.
     *
     * @return bool True if the admin bar should be shown, false otherwise.
     */
    public static function isAdminBarShowing()
    {
        $showAdminBar = (Option::get('menu_bar') && is_admin_bar_showing() && User::Access());

        /**
         * Filters whether to show the WordPress admin bar.
         *
         * @example add_filter('wp_statistics_show_admin_bar', '__return_false');
         */
        return apply_filters('wp_statistics_show_admin_bar', $showAdminBar);
    }
}