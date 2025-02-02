<?php
namespace WP_Statistics\Service\Admin\Metabox;

use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\AboutWPS;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\GoPremium;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\PostSummary;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\BrowserUsage;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TopCountries;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TopReferring;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\LatestVisitor;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\SearchEngines;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TopDeviceModel;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TrafficSummary;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TrafficOverview;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\CurrentlyOnline;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\MostVisitedPages;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\OperatingSystems;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\DailyTrafficTrend;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\MostActiveVisitors;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\DeviceUsageBreakdown;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\GlobalVisitorDistribution;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\PostVisitorsLocked;

class MetaboxHelper
{
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
        MostActiveVisitors::class,
        LatestVisitor::class,
        CurrentlyOnline::class,
        TopCountries::class,
        TopReferring::class,
        AboutWPS::class,
        GoPremium::class,
        GlobalVisitorDistribution::class,
        PostSummary::class,
        PostVisitorsLocked::class
    ];

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
        $metaboxes       = self::getMetaboxes();

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

        $currentScreen = get_current_screen()->id;

        // Get static metaboxes that belong to the current screen
        $staticMetaboxes = [];
        foreach (self::getActiveMetaboxes() as $metabox) {
            if (in_array($currentScreen, $metabox->getScreen()) && !$metabox->isStatic()) {
                $staticMetaboxes[$metabox->getKey()] = $metabox;
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
                $contextMetaboxes = array_filter($contextMetaboxes, function($metabox) use ($staticMetaboxes) {
                    return strpos($metabox, 'wp-statistics') !== false && isset($staticMetaboxes[$metabox]);
                });

                if (!empty($contextMetaboxes)) {
                    $metaboxes[$context] = array_values($contextMetaboxes);
                }
            }
        } else {
            // Group the metaboxes by context
            foreach ($staticMetaboxes as $metabox) {
                $metaboxes[$metabox->getContext()][] = $metabox->getKey();
            }
        }

        return $metaboxes;
    }
}