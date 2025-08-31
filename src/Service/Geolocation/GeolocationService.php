<?php

namespace WP_Statistics\Service\Geolocation;

use Exception;
use WP_Error;
use WP_STATISTICS\IP;

/**
 * GeolocationService Class
 *
 * This service class handles geolocation data, providing methods to fetch location data
 * based on IP addresses or other identifying information.
 *
 * @package   GeoLocationService
 * @version   1.0.0
 * @since     14.10.1
 * @author    Mostafa
 */
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
         * Check if the IP address is not valid (or is hashed), return default location
         */
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return $this->provider->getDefaultLocation();
        }

        /**
         * Check if the IP address is in a private range.
         * @review: If this is not necessary, remove it.
         */
        try {
            if (IP::checkIPRange(IP::$private_SubNets, $ipAddress)) {
                $location            = $this->provider->getDefaultLocation();
                $location['country'] = $this->provider->getPrivateCountryCode();

                return $location;
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
