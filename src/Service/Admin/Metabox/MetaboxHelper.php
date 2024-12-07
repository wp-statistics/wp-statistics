<?php
namespace WP_Statistics\Service\Admin\Metabox;

use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\DailyTrafficTrend;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TrafficSummary;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\ReferralsSearchEngines;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TopCountries;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\BrowserUsage;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\DeviceUsageBreakdown;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TopDeviceModel;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\OperatingSystems;

class MetaboxHelper
{
    public static $metaboxes = [
        TrafficSummary::class,
        BrowserUsage::class,
        OperatingSystems::class,
        DeviceUsageBreakdown::class,
        TopDeviceModel::class,
        DailyTrafficTrend::class,
        ReferralsSearchEngines::class,
        TopCountries::class
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