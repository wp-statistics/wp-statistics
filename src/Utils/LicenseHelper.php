<?php

namespace WP_Statistics\Utils;

use WP_Statistics\Service\Admin\LicenceDecorator;

class LicenseHelper
{
    /**
     * @return LicenceDecorator[]
     */
    public static function getAddOns()
    {
        $addOns           = apply_filters('wp_statistics_addons', array());
        $licenseDecorator = [];

        foreach ($addOns as $addOnSlug => $addOnName) {
            $licenseDecorator[] = new LicenceDecorator($addOnSlug, $addOnName);
        }

        return $licenseDecorator;
    }

    /**
     * @param $addOnSlug
     * @param $addOnName
     * @return LicenceDecorator
     */
    public static function getLicence($addOnSlug, $addOnName)
    {
        return new LicenceDecorator($addOnSlug, $addOnName);
    }
}
