<?php

namespace WP_Statistics\Service\Admin\Metabox;

use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\AboutWPS;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\GoPremium;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\PostSummary;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\BrowserUsage;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TopCountries;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TopReferring;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\SearchEngines;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TopDeviceModel;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TrafficSummary;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TrafficOverview;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\MostVisitedPages;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\OperatingSystems;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\DailyTrafficTrend;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\Visitors;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\DeviceUsageBreakdown;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\GlobalVisitorDistribution;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Utils\Request;

class MetaboxHelper
{
    /**
     * Rows per list widget on the WordPress Dashboard (compact set). The Overview page keeps its own limits.
     */
    const DASHBOARD_ROWS = 5;

    public static $metaboxes = [
        TrafficSummary::class,
        TrafficOverview::class,
        BrowserUsage::class,
        OperatingSystems::class,
        DeviceUsageBreakdown::class,
        TopDeviceModel::class,
        DailyTrafficTrend::class,
        SearchEngines::class,
        MostVisitedPages::class,
        Visitors::class, // Latest, Most active and Online now in one card (was three widgets before 14.17).
        TopCountries::class,
        TopReferring::class,
        AboutWPS::class,
        GoPremium::class,
        GlobalVisitorDistribution::class,
        PostSummary::class,
    ];

    /**
     * Is the widget being rendered for the WordPress Dashboard (wp-admin/index.php)?
     *
     * Widget bodies load over AJAX, so the screen comes from the `current_page` the
     * script sends (wps_js.global.page). The Overview page sends admin.php.
     *
     * @return bool
     */
    public static function isDashboardContext()
    {
        if (Request::isFrom('ajax')) {
            $currentPage = Request::get('current_page', [], 'array');

            return isset($currentPage['file']) && $currentPage['file'] === 'index.php';
        }

        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();

            return $screen && $screen->id === 'dashboard';
        }

        return false;
    }

    /**
     * Whether the "Get More with Premium Analytics" widget stays out.
     *
     * @return bool
     */
    public static function shouldHideGoPremium()
    {
        // The widget sells clicks, downloads, live visitors and goals: with Data Plus on, the user already has the main part.
        return LicenseHelper::isPremiumLicenseAvailable()
            || LicenseHelper::areAddonPromptsHidden()
            || !\WP_STATISTICS\User::Access('manage')
            || self::isDataPlusActive();
    }

    /**
     * Is Data Plus turned on (licensed or not)?
     *
     * @return bool
     */
    private static function isDataPlusActive()
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active('wp-statistics-data-plus/wp-statistics-data-plus.php');
    }

    /**
     * Retrieves a list of all metaboxes.
     *
     * @return BaseMetabox[]|string[]
     */
    public static function getMetaboxes()
    {
        return apply_filters('wp_statistics_metabox_list', self::$metaboxes);
    }

    /**
     * Get list of active metaboxes.
     *
     * @return BaseMetabox[]
     */
    public static function getActiveMetaboxes()
    {
        $activeMetaboxes = [];
        $skipClasses     = [];

        // No upsell widget with a premium license, under white label, or for users who cannot buy add-ons.
        if (self::shouldHideGoPremium()) {
            $skipClasses[] = GoPremium::class;
        }

        $metaboxes = array_filter(self::getMetaboxes(), function ($metabox) use ($skipClasses) {
            return !in_array($metabox, $skipClasses, true);
        });

        foreach ($metaboxes as $metabox) {
            if (!class_exists($metabox)) continue;

            $metabox = new $metabox();

            if (!$metabox->isActive()) continue;

            $activeMetaboxes[$metabox->getKey()] = $metabox;
        }

        return $activeMetaboxes;
    }

    /**
     * Returns a list of metaboxes which are specific to the current screen grouped by context.
     *
     * @return array
     */
    public static function getScreenMetaboxes()
    {
        $metaboxes = [];

        // Return early if there is no current screen
        if (!function_exists('get_current_screen')) {
            return $metaboxes;
        }

        $screen        = get_current_screen();
        $currentScreen = $screen->id;
        $isBlockEditor = method_exists($screen, 'is_block_editor') && $screen->is_block_editor();

        // Get screen metaboxes that belong to the current screen
        $screenMetaboxes = [];
        foreach (self::getActiveMetaboxes() as $metabox) {
            // Classic-only meta boxes are not printed in the block editor, so do not load their data there
            $callbackArgs = $metabox->getCallbackArgs();
            if ($isBlockEditor && !empty($callbackArgs['__back_compat_meta_box'])) {
                continue;
            }

            if (in_array($currentScreen, $metabox->getScreen()) && !$metabox->isStatic()) {
                $screenMetaboxes[$metabox->getKey()] = $metabox;
            }
        }

        // Get the stored metaboxes for current user
        $userMetaboxes = get_user_meta(get_current_user_id(), "meta-box-order_$currentScreen", true);

        // If there are stored metaboxes, use them
        if (!empty($userMetaboxes)) {
            $contexts = array_keys($userMetaboxes);

            foreach ($contexts as $context) {
                // Get the metaboxes for the current context
                $contextMetaboxes = explode(',', $userMetaboxes[$context]);

                // Remove any non wp-statistics metaboxes
                $contextMetaboxes = array_filter($contextMetaboxes, function ($metabox) use ($screenMetaboxes) {
                    return strpos($metabox, 'wp-statistics') !== false && isset($screenMetaboxes[$metabox]);
                });

                if (!empty($contextMetaboxes)) {
                    $metaboxes[$context] = array_values($contextMetaboxes);
                }
            }
        }

        // If there are no stored metaboxes, use the default
        foreach ($screenMetaboxes as $metabox) {
            $key     = $metabox->getKey();
            $context = $metabox->getContext();

            if (!isset($metaboxes[$context]) || !in_array($key, $metaboxes[$context])) {
                $metaboxes[$context][] = $metabox->getKey();
            }
        }

        return $metaboxes;
    }
}