<?php

namespace WP_Statistics\Service\Geolocation;

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
    public function getGeolocation(string $ipAddress): array
    {
        return $this->provider->fetchGeolocationData($ipAddress);
    }
}
