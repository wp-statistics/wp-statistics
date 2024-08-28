<?php

namespace WP_Statistics\Service\Geolocation;

use WP_Error;
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
