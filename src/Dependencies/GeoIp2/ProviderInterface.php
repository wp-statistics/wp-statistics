<?php

namespace WP_Statistics\Dependencies\GeoIp2;

interface ProviderInterface
{
    /**
     * @param string $ipAddress an IPv4 or IPv6 address to lookup
     *
     * @return \WP_Statistics\Dependencies\GeoIp2\Model\Country a Country model for the requested IP address
     */
    public function country($ipAddress);

    /**
     * @param string $ipAddress an IPv4 or IPv6 address to lookup
     *
     * @return \WP_Statistics\Dependencies\GeoIp2\Model\City a City model for the requested IP address
     */
    public function city($ipAddress);
}
