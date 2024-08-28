<?php

namespace WP_Statistics\Service\Geolocation;

use WP_Error;
use WP_Statistics\Service\Geolocation\Provider\GeoServiceProviderInterface;
use WP_Statistics\Service\Geolocation\Provider\MaxmindGeoIPProvider;
use WP_Statistics\Service\Geolocation\Provider\DbIpProvider;

class GeolocationFactory
{
    /**
     * Get geolocation data for a given IP address using the configured provider.
     *
     * @param string $ipAddress
     * @return array
     */
    public static function getLocation(string $ipAddress)
    {
        $provider           = self::getProviderInstance();
        $geolocationService = new GeolocationService($provider);

        return $geolocationService->getGeolocation($ipAddress);
    }

    /**
     * Download the geolocation database using the configured provider.
     *
     * @return bool|WP_Error
     */
    public static function downloadDatabase()
    {
        $provider           = self::getProviderInstance();
        $geolocationService = new GeolocationService($provider);

        return $geolocationService->downloadDatabase();
    }

    /**
     * Get an instance of the configured geolocation provider.
     *
     * @return GeoServiceProviderInterface
     */
    public static function getProviderInstance()
    {
        $providerName = apply_filters('wp_statistics_geolocation_provider_name', 'maxmind');

        switch ($providerName) {
            case 'maxmind':
                return new MaxmindGeoIPProvider();

            case 'dbip':
                return new DbIpProvider();

            default:
                return new MaxmindGeoIPProvider();
        }
    }
}
