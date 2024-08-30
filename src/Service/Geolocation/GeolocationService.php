<?php

namespace WP_Statistics\Service\Geolocation;

use Exception;
use WP_Error;
use WP_STATISTICS\IP;
use WP_Statistics\Service\Geolocation\Provider\GeoServiceProviderInterface;

class GeolocationService
{
    /**
     * @var GeoServiceProviderInterface
     */
    protected $provider;

    /**
     * GeolocationService constructor.
     *
     * @param GeoServiceProviderInterface $provider
     */
    public function __construct(GeoServiceProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get geolocation data for a given IP address.
     *
     * @param string $ipAddress
     * @return array
     */
    public function getGeolocation(string $ipAddress)
    {
        /**
         * Check if the IP address is a hashed IP address, then, return the default location.
         */
        if (strpos($ipAddress, IP::$hash_ip_prefix) !== false) {
            return $this->provider->getDefaultLocation();
        }

        /**
         * Check if the IP address is in a private range.
         * @review: If this is not necessary, remove it.
         */
        try {
            if (IP::CheckIPRange(IP::$private_SubNets)) {
                return $this->provider->getDefaultCountryCode();
            }
        } catch (Exception $e) {
            return $this->provider->getDefaultLocation();
        }

        return $this->provider->fetchGeolocationData($ipAddress);
    }

    /**
     * Download the geolocation database.
     *
     * @return bool|WP_Error
     */
    public function downloadDatabase()
    {
        return $this->provider->downloadDatabase();
    }
}
