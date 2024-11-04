<?php

namespace WP_Statistics\Service\Geolocation;

use WP_Error;
use WP_Statistics\Service\Geolocation\Provider\MaxmindGeoIPProvider;

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
     * @return MaxmindGeoIPProvider
     */
    public static function getProviderInstance()
    {
        /**
         * Filter the geolocation provider name. This allows developers to change the provider used for geolocation.
         */
        $providerName = apply_filters('wp_statistics_geolocation_provider', MaxmindGeoIPProvider::class);

        // If the provider class exists, instantiate it
        if (class_exists($providerName)) {
            return new $providerName();
        }

        return new MaxmindGeoIPProvider();
    }
}
