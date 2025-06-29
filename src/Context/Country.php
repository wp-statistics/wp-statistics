<?php

namespace WP_Statistics\Context;

use WP_STATISTICS\TimeZone;

/**
 * Helper for country‑related utilities.
 *
 * @package WP_Statistics\Context
 * @since   15.0.0
 */
final class Country
{
    /**
     * Derive the site’s country code from the configured timezone string.
     *
     * @return string country code or an empty string if
     *                the timezone cannot be mapped.
     */
    public static function getByTimeZone()
    {
        $timezone    = get_option('timezone_string');
        $countryCode = TimeZone::getCountry($timezone);

        return $countryCode;
    }

}