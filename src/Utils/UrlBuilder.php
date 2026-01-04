<?php

namespace WP_Statistics\Utils;

/**
 * URL Builder utility for WP Statistics admin pages.
 *
 * Provides centralized URL generation for React SPA routes and legacy admin pages,
 * avoiding hardcoded URL patterns throughout the codebase.
 *
 * @since 15.0.0
 */
class UrlBuilder
{
    /**
     * Main menu slug for WP Statistics.
     */
    private const MENU_SLUG = 'wp-statistics';

    /**
     * Legacy menu slug prefix.
     */
    private const LEGACY_PREFIX = 'wps_';

    /**
     * Build React SPA route URL (hash-based).
     *
     * Example: admin.php?page=wp-statistics#/overview
     *
     * @param string $route The route path (e.g., 'overview', 'settings/general').
     * @return string The full admin URL with hash route.
     */
    public static function reactRoute(string $route = ''): string
    {
        $route = ltrim($route, '/');
        $hash = $route ? '#/' . $route : '';

        return admin_url('admin.php?page=' . self::MENU_SLUG . $hash);
    }

    /**
     * Build legacy admin page URL.
     *
     * Example: admin.php?page=wps_settings_page&tab=general
     *
     * @param string $page   The legacy page slug (e.g., 'settings_page', 'plugins_page').
     * @param array  $params Optional query parameters.
     * @return string The full admin URL.
     */
    public static function legacyPage(string $page, array $params = []): string
    {
        $fullSlug = self::LEGACY_PREFIX . $page;
        $url = admin_url('admin.php?page=' . $fullSlug);

        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Build overview/dashboard URL.
     *
     * @return string The overview page URL.
     */
    public static function overview(): string
    {
        return self::reactRoute('overview');
    }

    /**
     * Build settings URL.
     *
     * @param string $tab The settings tab (e.g., 'general', 'privacy', 'notifications').
     * @return string The settings page URL.
     */
    public static function settings(string $tab = 'general'): string
    {
        return self::reactRoute('settings/' . $tab);
    }

    /**
     * Build page analytics URL.
     *
     * @param int $pageId The WordPress page/post ID.
     * @return string The page analytics URL.
     */
    public static function pageAnalytics(int $pageId): string
    {
        return self::reactRoute("page-analytics/{$pageId}");
    }

    /**
     * Build author analytics URL.
     *
     * @param int $authorId The WordPress author/user ID.
     * @return string The author analytics URL.
     */
    public static function authorAnalytics(int $authorId): string
    {
        return self::reactRoute("author-analytics/{$authorId}");
    }

    /**
     * Build category analytics URL.
     *
     * @param int $categoryId The WordPress category/term ID.
     * @return string The category analytics URL.
     */
    public static function categoryAnalytics(int $categoryId): string
    {
        return self::reactRoute("category-analytics/{$categoryId}");
    }

    /**
     * Build geographic analytics URL.
     *
     * @param string $countryCode The ISO country code.
     * @return string The geographic analytics URL.
     */
    public static function geoAnalytics(string $countryCode): string
    {
        return self::reactRoute("geographic/{$countryCode}");
    }

    /**
     * Build visitor profile URL.
     *
     * @param string $visitorHash The visitor's unique hash identifier.
     * @return string The visitor profile URL.
     */
    public static function visitorProfile(string $visitorHash): string
    {
        return self::reactRoute("visitor/{$visitorHash}");
    }

    /**
     * Build plugins/add-ons page URL (legacy).
     *
     * @return string The plugins page URL.
     */
    public static function pluginsPage(): string
    {
        return self::legacyPage('plugins_page');
    }

    /**
     * Build privacy audit page URL (legacy).
     *
     * @return string The privacy audit page URL.
     */
    public static function privacyAudit(): string
    {
        return self::legacyPage('privacy-audit');
    }

    /**
     * Build optimization page URL (legacy).
     *
     * @return string The optimization page URL.
     */
    public static function optimization(): string
    {
        return self::legacyPage('optimization_page');
    }

    /**
     * Get the main menu slug.
     *
     * @return string The main menu slug.
     */
    public static function getMenuSlug(): string
    {
        return self::MENU_SLUG;
    }

    /**
     * Get the legacy menu slug prefix.
     *
     * @return string The legacy prefix.
     */
    public static function getLegacyPrefix(): string
    {
        return self::LEGACY_PREFIX;
    }

    /**
     * Build a custom admin URL with the WP Statistics base.
     *
     * @param string $hash   The hash route (without #/).
     * @param array  $params Optional query parameters to add to the base URL.
     * @return string The full admin URL.
     */
    public static function custom(string $hash = '', array $params = []): string
    {
        $url = admin_url('admin.php?page=' . self::MENU_SLUG);

        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }

        if (!empty($hash)) {
            $hash = ltrim($hash, '/');
            $url .= '#/' . $hash;
        }

        return $url;
    }
}
