<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Country;
use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_STATISTICS\IP;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;
use WP_Statistics\Service\Geolocation\GeolocationFactory;

class hitsmap extends MetaBoxAbstract
{
    public static function get($args = array())
    {
        $args = apply_filters('wp_statistics_meta_box_hitsmap_args', $args);

        self::filterByDate($args);

        $range      = array_keys(self::$daysList);
        $chartArgs  = [
            'date' => [
                'from'  => reset($range),
                'to'    => end($range)
            ]
        ];

        $chartData = ChartDataProviderFactory::mapChart($chartArgs)->getData();

        return self::response($chartData);
    }
}
