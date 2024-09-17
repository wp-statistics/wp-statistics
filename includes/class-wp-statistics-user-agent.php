<?php

namespace WP_STATISTICS;

use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;

/**
 * @deprecated This class will be deprecated soon, use WP_Statistics\Service\Analytics\UserAgent instead.
 */
class UserAgent
{
    /**
     * Get All Browser List For Detecting
     *
     * @param bool $all
     * @area utility
     * @return array|mixed
     */
    public static function BrowserList($all = true)
    {
        _deprecated_function('BrowserList', '14.11', 'DeviceHelper::getBrowserList');

        return DeviceHelper::getBrowserList($all);
    }

    /**
     * Returns browser logo.
     *
     * @param string $browser Browser name.
     * @param string $browser Browser name.
     *
     * @return  string              Logo URL, or URL of an unknown browser icon.
     */
    public static function getBrowserLogo($browser)
    {
        _deprecated_function('getBrowserLogo', '14.11', 'DeviceHelper::getBrowserLogo');

        return DeviceHelper::getBrowserLogo($browser);
    }

    /**
     * Returns platform/OS logo.
     *
     * @param string $platform Platform name.
     *
     * @return  string              Logo URL, or URL of an unknown browser icon.
     */
    public static function getPlatformLogo($platform)
    {
        _deprecated_function('getPlatformLogo', '14.11', 'DeviceHelper::getPlatformLogo');

        return DeviceHelper::getPlatformLogo($platform);
    }
}
