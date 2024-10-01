<?php

namespace WP_STATISTICS;

use Exception;
use WP_Statistics\Service\Geolocation\GeolocationFactory;

/**
 * @deprecated This class is deprecated and should not be used in new development. It remains for backward compatibility of Add-ons.
 */
class GeoIP
{
    /**
     * Retrieves the geolocation information for a given IP address.
     *
     * Caches the location information to avoid redundant lookups.
     *
     * @param bool|string $ip The IP address to lookup. Defaults to the user's IP.
     * @return array|null[] The location.
     */
    public static function getLocation($ip)
    {
        _deprecated_function(__METHOD__, '14.11', 'GeolocationFactory::getLocation()');

        return GeolocationFactory::getLocation($ip);
    }

    /**
     * Retrieves the country information for a given IP address.
     *
     * @param bool|string $ip The IP address to lookup. Defaults to the user's IP.
     * @return string|null The country code or detail requested, or null on failure.
     * @throws Exception If there is an issue during GeoIP lookup.
     */
    public static function getCountry($ip = false)
    {
        $location = self::getLocation($ip);

        return $location['country'];
    }

    /**
     * Downloads the GeoIP database from MaxMind.
     *
     * @return mixed Array containing status and notice messages.
     * @deprecated This method is deprecated and should not be used in new development. use GeolocationFactory::downloadDatabase() instead.
     */
    public static function download()
    {
        _deprecated_function(__METHOD__, '14.11', 'GeolocationFactory::downloadDatabase()');

        return GeolocationFactory::downloadDatabase();
    }

    /**
     * Retrieves city information based on a given IP address.
     *
     * @param string|bool $ip The IP address to lookup. Defaults to the user's IP.
     * @param bool $dataScope Whether to include region and continent information.
     * @return array|string The city name or an array of location details.
     */
    public static function getCity($ip = false, $dataScope = false)
    {
        $location = self::getLocation($ip);

        // Retrieve region and continent if requested.
        if ($dataScope) {
            return [
                'city'      => $location['city'],
                'region'    => $location['region'],
                'continent' => $location['continent']
            ];
        }

        return $location['city'];
    }
}
