<?php

namespace WP_Statistics\Service\Geolocation\Provider;

interface GeoServiceProviderInterface
{
    /**
     * Fetch geolocation data for the given IP address.
     *
     * @param string $ipAddress
     * @return array
     */
    public function fetchGeolocationData(string $ipAddress): array;

    /**
     * Get the download URL for the GeoIP database.
     *
     * @return string
     */
    public function getDownloadUrl(): string;

    /**
     * Download the GeoIP database, extract it, and handle updates.
     *
     * @return array
     */
    public function downloadDatabase(): array;
}
