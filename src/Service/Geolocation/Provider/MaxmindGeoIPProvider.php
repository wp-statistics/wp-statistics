<?php

namespace WP_Statistics\Service\Geolocation\Provider;

use Exception;
use WP_Statistics;
use WP_STATISTICS\Option;
use WP_Statistics\Dependencies\GeoIp2\Database\Reader;

class MaxmindGeoIPProvider extends AbstractGeoIPProvider
{
    /**
     * @var \WP_Statistics\Dependencies\GeoIp2\Database\Reader|null
     */
    protected $reader = null;

    /**
     * @var string
     */
    protected $databaseFileName = 'GeoLite2-City.mmdb';

    /**
     * MaxmindGeoIPProvider constructor.
     */
    public function __construct()
    {
        // Attempt to initialize the reader, downloading the database if necessary
        $this->initializeReader();
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
            // Ensure the reader is initialized
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
     * Attempts to download the database if it doesn't exist.
     *
     * @throws Exception
     */
    protected function initializeReader()
    {
        if ($this->reader !== null) {
            return; // Return early if the reader is already initialized
        }

        try {
            $databasePath = $this->getDatabasePath();
            if (!file_exists($databasePath)) {
                WP_Statistics::log("GeoIP database not found. Attempting to download...");

                $downloadResult = $this->downloadDatabase();
                if (!$downloadResult['status']) {
                    throw new Exception($downloadResult['notice']);
                }
            }

            $this->reader = new Reader($databasePath);
        } catch (Exception $e) {
            $errorMessage = "Failed to initialize GeoIP reader: " . $e->getMessage();
            WP_Statistics::log($errorMessage); // Log the error for debugging
        }
    }

    /**
     * Get the download URL for the Maxmind GeoIP database.
     *
     * @return string
     */
    public function getDownloadUrl(): string
    {
        $licenseKey = Option::get('geoip_license_key') && Option::get('geoip_license_type') == 'user-license'
            ? Option::get('geoip_license_key')
            : null;

        return $licenseKey
            ? "https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key={$licenseKey}&suffix=tar.gz"
            : 'https://cdn.jsdelivr.net/npm/geolite2-city/GeoLite2-City.mmdb.gz';
    }

    /**
     * Download the GeoIP database, extract it, and handle updates.
     *
     * @return array
     */
    public function downloadDatabase(): array
    {
        $result     = ['status' => false];
        $gzFilePath = $this->getFilePath('GeoLite2-City.mmdb.gz');

        try {
            $downloadUrl = $this->getDownloadUrl();
            $response    = wp_remote_get($downloadUrl, [
                'stream'   => true,
                'filename' => $gzFilePath,
                'timeout'  => 120,
            ]);

            // Check the HTTP status code
            $statusCode = wp_remote_retrieve_response_code($response);
            if ($statusCode !== 200) {
                throw new Exception(sprintf(__('Unexpected HTTP status code %1$d while downloading GeoIP database from: %2$s', 'wp-statistics'), $statusCode, $downloadUrl));
            }

            if (is_wp_error($response)) {
                throw new Exception(sprintf(__('Error downloading GeoIP database from: %1$s - %2$s', 'wp-statistics'), $downloadUrl, $response->get_error_message()));
            }

            $dbFile = $this->getDatabasePath();
            $this->extractGzFile($gzFilePath, $dbFile);

            wp_delete_file($gzFilePath); // Clean up the temporary file

            $result['status'] = true;
            $result['notice'] = __('GeoIP Database successfully updated!', 'wp-statistics');

            // Update options and send notifications
            $this->updateLastDownloadTimestamp();
            $this->batchUpdateIncompleteGeoIp();
            $this->sendGeoIpUpdateEmail($result['notice']);

        } catch (Exception $e) {
            wp_delete_file($gzFilePath); // Ensure temporary file is deleted in case of an error

            $result['notice'] = sprintf(__('Error: %1$s', 'wp-statistics'), $e->getMessage());
            WP_Statistics::log($result['notice']); // Log the error for debugging
        }

        return $result;
    }

    /**
     * Extract gzipped file to the specified destination.
     *
     * @param string $gzFilePath
     * @param string $destinationPath
     * @return void
     * @throws Exception
     */
    protected function extractGzFile(string $gzFilePath, string $destinationPath)
    {
        try {
            $phar = new \PharData($gzFilePath);
            $phar->decompress(); // Decompresses .gz to .tar

            $tarPath = str_replace('.gz', '', $gzFilePath);
            $pharTar = new \PharData($tarPath);
            $pharTar->extractTo(dirname($destinationPath), null, true);

            // Clean up the .tar file
            wp_delete_file($tarPath);
        } catch (Exception $e) {
            throw new Exception("Failed to extract the database file: " . $e->getMessage());
        }
    }
}
