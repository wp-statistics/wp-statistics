<?php

namespace WP_Statistics\Service\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_STATISTICS\User;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

/**
 * Old links and bookmarks to an add-on page (Link Tracker, Email log, Goals...) while that add-on is
 * turned off or removed: WordPress only knows the page while the add-on runs, so it would print a bare
 * "Sorry, you are not allowed to access this page." Instead:
 *
 * - white label / hidden add-on prompts: a neutral "This feature is not available" (403);
 * - users with the manage capability: the Add-ons page, where the add-on can be turned on;
 * - other users: "Ask a site admin to turn on <add-on>." (403).
 *
 * Nothing changes while the add-on is active (then it is a real permission problem) or for users without
 * WP Statistics read access.
 *
 * Reports hidden with Customization > "Disable menus" get the same care: "This report is turned off in
 * Settings → Customization." (with a link) for settings managers, "This report is not available." for others.
 * No add-on or premium wording, so it also fits white label.
 */
class AddonPageFallback
{
    public function register()
    {
        add_action('admin_page_access_denied', [$this, 'handle']);
    }

    /**
     * Add-on admin pages: `page` query value => [add-on slug, add-on name].
     *
     * @return array
     */
    public static function getPages()
    {
        $pages = [
            'wps_link_tracker_page'      => ['wp-statistics-data-plus', 'Data Plus'],
            'wps_download_tracker_page'  => ['wp-statistics-data-plus', 'Data Plus'],
            'wps_advanced_reporting_log' => ['wp-statistics-advanced-reporting', 'Advanced Reporting'],
            'wps_goals_page'             => ['wp-statistics-marketing', 'Marketing'],
            'wps_campaign_builder_page'  => ['wp-statistics-marketing', 'Marketing'],
        ];

        /**
         * Filters the add-on pages that get a friendly page instead of WordPress's bare access error.
         *
         * @param array $pages `page` query value => [add-on slug, add-on name].
         */
        return (array)apply_filters('wp_statistics_addon_page_fallbacks', $pages);
    }

    /**
     * What to do for a denied admin page.
     *
     * @param string $page `page` query value.
     * @param bool $addonActive
     * @param bool $promptsHidden
     * @param bool $canRead
     * @param bool $canManage
     *
     * @return string '' (leave it to WordPress), 'unavailable', 'redirect' or 'ask-admin'.
     */
    public static function decide($page, $addonActive, $promptsHidden, $canRead, $canManage)
    {
        if (!isset(self::getPages()[$page]) || $addonActive || !$canRead) {
            return '';
        }

        if ($promptsHidden) {
            return 'unavailable';
        }

        return $canManage ? 'redirect' : 'ask-admin';
    }

    /**
     * `page` query values of the reports hidden by Customization > Disable menus.
     *
     * @return string[]
     */
    public static function getDisabledMenuPages()
    {
        if (!\WP_STATISTICS\Helper::isAddOnActive('customization')) {
            return [];
        }

        $pages = [];
        foreach ((array)\WP_STATISTICS\Option::getByAddon('disable_menus', 'customization', []) as $key) {
            if (!is_string($key) || $key === '') {
                continue;
            }
            // Menu keys use underscores, a few page slugs use dashes (content_analytics => wps_content-analytics_page).
            $pages[] = Menus::get_page_slug($key);
            $pages[] = Menus::get_page_slug(str_replace('_', '-', $key));
        }

        return array_values(array_unique($pages));
    }

    /**
     * What to do for a denied page that Customization hid.
     *
     * @param string $page
     * @param string[] $disabledPages
     * @param bool $canRead
     * @param bool $canManage
     *
     * @return string '' (leave it to WordPress), 'turned-off-admin' or 'turned-off'.
     */
    public static function decideDisabled($page, $disabledPages, $canRead, $canManage)
    {
        if (!$canRead || !in_array($page, (array)$disabledPages, true)) {
            return '';
        }

        return $canManage ? 'turned-off-admin' : 'turned-off';
    }

    public function handle()
    {
        $page  = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $pages = self::getPages();

        $disabled = self::decideDisabled($page, self::getDisabledMenuPages(), User::Access('read'), User::Access('manage'));
        if ($disabled === 'turned-off-admin') {
            $settingsUrl = Menus::admin_url('settings', ['tab' => 'customization-settings']);
            wp_die(
                '<p>' . esc_html__('This report is turned off in Settings → Customization.', 'wp-statistics') . '</p><p><a href="' . esc_url($settingsUrl) . '">' . esc_html__('Open Customization settings', 'wp-statistics') . '</a></p>',
                esc_html__('Not available', 'wp-statistics'),
                ['response' => 403, 'back_link' => true]
            );
        }
        if ($disabled === 'turned-off') {
            wp_die(esc_html__('This report is not available.', 'wp-statistics'), esc_html__('Not available', 'wp-statistics'), ['response' => 403, 'back_link' => true]);
        }

        if (!isset($pages[$page])) {
            return;
        }

        list($slug, $name) = $pages[$page];

        $action = self::decide(
            $page,
            \WP_STATISTICS\Helper::isAddOnActive(str_replace('wp-statistics-', '', $slug)),
            LicenseHelper::areAddonPromptsHidden(),
            User::Access('read'),
            User::Access('manage')
        );

        if ($action === 'redirect') {
            wp_safe_redirect(Menus::admin_url('plugins'));
            exit;
        }

        if ($action === 'unavailable') {
            wp_die(esc_html__('This feature is not available.', 'wp-statistics'), esc_html__('Not available', 'wp-statistics'), ['response' => 403, 'back_link' => true]);
        }

        if ($action === 'ask-admin') {
            /* translators: %1$s: Add-on name. */
            wp_die(esc_html(sprintf(__('This report needs %1$s. Ask a site admin to turn on %1$s.', 'wp-statistics'), $name)), esc_html__('Not available', 'wp-statistics'), ['response' => 403, 'back_link' => true]);
        }
    }
}
