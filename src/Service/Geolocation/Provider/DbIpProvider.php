<?php

namespace WP_Statistics\Service\Geolocation\Provider;

use Exception;

class DbIpProvider extends AbstractGeoIPProvider
{
    /**
     * @var string
     */
    protected $databasePath;

    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * DbIpProvider constructor.
     */
    public function __construct()
    {
        $this->apiKey       = get_option('dbip_api_key'); // todo
        $uploadDir          = wp_upload_dir();
        $this->databasePath = $uploadDir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR . '/dbip-country-lite.mmdb';
    }

    /**
     * Fetch geolocation data for the given IP address.
     *
     * @param string $ipAddress
     * @return array
     */
    public function fetchGeolocationData(string $ipAddress): array
    {
        try {
            // Implement logic to read from the DB-IP database
            return [
                'ip'        => $ipAddress,
                'country'   => 'Example Country',
                'continent' => 'Example Continent',
                'region'    => 'Example Region',
                'city'      => 'Example City',
                'latitude'  => '0.0000',
                'longitude' => '0.0000',
            ];

        } catch (Exception $e) {
            return [
                'ip'        => $ipAddress,
                'country'   => null,
                'continent' => null,
                'region'    => null,
                'city'      => null,
                'latitude'  => null,
                'longitude' => null,
                'error'     => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the download URL for the DB-IP database.
     *
     * @return string
     */
    public function getDownloadUrl(): string
    {
        return $this->apiKey
            ? "https://download.db-ip.com/free/dbip-country-lite.mmdb.gz?api_key={$this->apiKey}"
            : 'https://download.db-ip.com/free/dbip-country-lite.mmdb.gz';
    }
}
