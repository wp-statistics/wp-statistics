<?php

namespace WP_STATISTICS;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;


class Meta_Box
{
    /**
     * Meta Box Class namespace
     *
     * @var string
     */
    public static $namespace = "\\WP_Statistics\\MetaBox\\";

    /**
     * Meta Box Setup Key
     *
     * @param $key
     * @return string
     */
    public static function getMetaBoxKey($key)
    {
        return 'wp-statistics-' . $key . '-widget';
    }

    /**
     * Load MetaBox
     *
     * @param $key
     * @return callable
     */
    public static function LoadMetaBox($key)
    {
        return function () {
            return null;
        };
    }

    /**
     * Get Admin Meta Box List
     *
     * @param bool $meta_box
     * @return array|mixed
     */
    public static function getList($meta_box = false)
    {
        return array();
    }

    /**
     * Check Exist Meta Box Class
     *
     * @param $meta_box
     * @return bool
     */
    public static function metaBoxClassExist($meta_box)
    {
        return class_exists(self::getMetaBoxClass($meta_box));
    }

    /**
     * Get Meta Box Class name
     *
     * @param $meta_box
     * @return string
     */
    public static function getMetaBoxClass($meta_box)
    {
        return apply_filters('wp_statistics_meta_box_class', self::$namespace . str_replace("-", "_", $meta_box), $meta_box);
    }

}