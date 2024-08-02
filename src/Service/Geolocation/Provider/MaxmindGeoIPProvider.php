<?php

namespace WP_Statistics\Service\Geolocation\Provider;

use GeoIp2\Database\Reader;
use Exception;
use WP_STATISTICS\Option;

class MaxmindGeoIPProvider extends AbstractGeoIPProvider
{
    /**
     * @var \WP_Statistics\Dependencies\GeoIp2\Database\Reader|null
     */
    protected $reader = null;

    /**
     * @var string
     */
    protected $databasePath;

    /**
     * @var string|null
     */
    protected $licenseKey;

    /**
     * MaxmindGeoIPProvider constructor.
     */
    public function __construct()
    {
        $this->licenseKey   = Option::get('geoip_license_key') && Option::get('geoip_license_type') == 'user-license' ? Option::get('geoip_license_key') : null;
        $uploadDir          = wp_upload_dir();
        $this->databasePath = $uploadDir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR . '/GeoLite2-City.mmdb';

        if (file_exists($this->databasePath)) {
            $this->initializeReader();
        }
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
            if ($this->reader === null) {
                $this->initializeReader();
            }

            $record = $this->reader->city($ipAddress);

            return [
                'ip'        => $ipAddress,
                'country'   => $record->country->name,
                'continent' => $record->continent->name,
                'region'    => $record->mostSpecificSubdivision->name,
                'city'      => $record->city->name,
                'latitude'  => $record->location->latitude,
                'longitude' => $record->location->longitude,
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
     * Initialize the GeoIP Reader.
     *
     * @throws Exception
     */
    protected function initializeReader()
    {
        if ($this->reader === null) {
            try {
                $this->reader = new \WP_Statistics\Dependencies\GeoIp2\Database\Reader($this->databasePath);
            } catch (Exception $e) {
                throw new Exception("Failed to initialize GeoIP reader: " . $e->getMessage());
            }
        }
    }

    /**
     * Get the download URL for the Maxmind GeoIP database.
     *
     * @return string
     */
    public function getDownloadUrl(): string
    {
        return $this->licenseKey
            ? "https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key={$this->licenseKey}&suffix=tar.gz"
            : 'https://cdn.jsdelivr.net/npm/geolite2-city/GeoLite2-City.mmdb.gz';
    }
}
