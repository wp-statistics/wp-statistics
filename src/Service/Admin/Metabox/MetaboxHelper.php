<?php
namespace WP_Statistics\Service\Admin\Metabox;

use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\DailyTrafficTrend;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TrafficSummary;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\SearchEngines;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\MostVisitedPages;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\MostActiveVisitors;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\LatestVisitor;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TopCountries;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TopReferring;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\BrowserUsage;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\DeviceUsageBreakdown;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\CurrentlyOnline;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TopDeviceModel;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\OperatingSystems;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\AboutWPS;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\GoPremium;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\GlobalVisitorDistribution;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\PostSummary;

class MetaboxHelper
{
    public static $metaboxes = [
        TrafficSummary::class,
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
        PostSummary::class
    ];

    /**
     * Retrieves a list of all metaboxes.
     *
     * @return BaseMetabox[]
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
}