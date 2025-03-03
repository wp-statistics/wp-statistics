<?php

namespace WP_Statistics\Service\Geolocation\Provider;

use Exception;
use WP_Statistics;
use WP_Error;
use WP_Statistics\Components\RemoteRequest;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Geolocation\AbstractGeoIPProvider;
use WP_Statistics\Dependencies\GeoIp2\Database\Reader;

class DbIpProvider extends AbstractGeoIPProvider
{
    /**
     * @var Reader|null
     */
    protected $reader = null;

    /**
     * @var string
     */
    protected $databaseFileName = 'dbip-city-lite.mmdb';

    /**
     * DbIpProvider constructor.
     */
    public function __construct()
    {
        $this->initializeReader();
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
        if (!empty($this->reader) && method_exists($this->reader, 'city')) {
            return; // Return early if the reader is already initialized
        }

        try {
            // Check if the GeoIP database exists and download it immediately.
            if (!$this->isDatabaseExist()) {
                $this->downloadDatabase();
            }

            /**
             * Initialize the GeoIP reader.
             */
            $this->reader = new Reader($this->getDatabasePath());

        } catch (Exception $e) {
            $errorMessage = "Failed to initialize GeoIP reader: " . $e->getMessage();
            WP_Statistics::log($errorMessage); // Log the error for debugging
        }
    }

    /**
     * Fetch geolocation data for the given IP address.
     *
     * @param string $ipAddress
     * @return array
     */
    public function fetchGeolocationData(string $ipAddress)
    {
        $this->initializeReader();

        if (empty($this->reader) || !method_exists($this->reader, 'city')) {
            throw new Exception('GeoIP database is corrupted.');
        }

        $record = $this->reader->city($ipAddress);

        try {
            return [
               'country'       => $record->country->name,
                'country_code' => $record->country->isoCode,
                'continent'    => $record->continent->name,
                'region'       => $record->mostSpecificSubdivision->name,
                'city'         => $record->city->name,
                'latitude'     => $record->location->latitude,
                'longitude'    => $record->location->longitude,
                'postal_code'  => $record->postal->code,
            ];

        } catch (Exception $e) {
            return $this->getDefaultLocation();
        }
    }

    /**
     * Get the download URL for the DB-IP database.
     *
     * @todo The default url should be updated to js-deliver.
     * @return string
     */
    public function getDownloadUrl()
    {
        $licenseKey = Option::get('geoip_dbip_license_key_option') && Option::get('geoip_license_type') == 'user-license'
            ? Option::get('geoip_dbip_license_key_option')
            : null;
        
        $downloadUrl = '';

        if ($licenseKey) {
            $remoteRequest = new RemoteRequest("https://db-ip.com/account/{$licenseKey}/db/ip-to-location/mmdb/url", 'GET');
            $downloadUrl   = $remoteRequest->execute();
        }

        $defaultUrl = $licenseKey
            ? $downloadUrl
            : 'https://cdn.jsdelivr.net/npm/dbip-city-lite/dbip-city-lite.mmdb.gz';

        return $this->getFilteredDownloadUrl($defaultUrl);
    }

    public function downloadDatabase()
    {
        $gzFilePath = $this->getFilePath('dbip-city-lite.mmdb.gz');
        set_time_limit(0);

        try {
            $downloadUrl = $this->getDownloadUrl();
            $remoteRequest = new RemoteRequest(
                $downloadUrl,
                'GET',
                [],
                [
                    'stream'   => true,
                    'filename' => $gzFilePath,
                    'timeout'  => 300,
                ]
            );

            $remoteRequest->execute(false, false);

            $response     = $remoteRequest->getResponse();
            $responseCode = $remoteRequest->getResponseCode();

            if ($responseCode !== 200) {
                throw new Exception(sprintf(__('Unexpected HTTP status code %1$d while downloading GeoIP database from: %2$s', 'wp-statistics'), $responseCode, $downloadUrl));
            }

            if (is_wp_error($response)) {
                throw new Exception(sprintf(__('Error downloading GeoIP database from: %1$s - %2$s', 'wp-statistics'), $downloadUrl, $response->get_error_message()));
            }

            $dbFile = $this->getDatabasePath();

            $this->extractGzFile($gzFilePath, $dbFile);
            $this->deleteFile($gzFilePath);

            // Update options and send notifications
            $this->updateLastDownloadTimestamp();
            $this->batchUpdateIncompleteGeoIp();
        } catch (Exception $e) {
            $this->deleteFile($gzFilePath);

            WP_Statistics::log($e->getMessage());

            return new WP_Error('error', $e->getMessage());
        }

        return true;
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
            $gzHandle = gzopen($gzFilePath, 'rb');
            if (!$gzHandle) {
                throw new Exception(__('Failed to open GZ archive.', 'wp-statistics'));
            }

            $dbFileHandle = fopen($destinationPath, 'wb');
            if (!$dbFileHandle) {
                gzclose($gzHandle);
                throw new Exception(__('Failed to open destination file for writing.', 'wp-statistics'));
            }

            while (!gzeof($gzHandle)) {
                fwrite($dbFileHandle, gzread($gzHandle, 4096));
            }

            gzclose($gzHandle);
            fclose($dbFileHandle);

            if (!file_exists($destinationPath)) {
                throw new Exception(__('Error extracting GeoIP database file.', 'wp-statistics'));
            }

        } catch (Exception $e) {
            throw new Exception("Failed to extract the database file: " . $e->getMessage());
        }
    }

    public function getDatabaseType()
    {
        $reader = $this->reader;

        if (!$reader) {
            return false;
        }

        return $reader->metadata()->databaseType;
    }

    public function validateDatabaseFile()
    {
        try {
            // Ensure the database file exists
            if (!$this->isDatabaseExist()) {
                throw new Exception(__('GeoIP database does not exist.', 'wp-statistics'));
            }

            if (empty($this->reader) || !method_exists($this->reader, 'metadata')) {
                throw new Exception(
                    sprintf(__('Failed to initialize GeoIP reader or invalid database file. Please remove the existing database file at %s and let the plugin redownload it.', 'wp-statistics'), $this->getDatabasePath())
                );
            }

            // Verify the database type and metadata
            $databaseType = $this->reader->metadata()->databaseType;
            
            if (! in_array($databaseType, ['DBIP-Location (compat=City)', 'DBIP-City-Lite'], true)) {
                throw new Exception(sprintf(__('Unexpected database type %s', 'wp-statistics'), $databaseType));
            }

            return true;

        } catch (Exception $e) {
            return new WP_Error('error', $e->getMessage());
        }
    }
}
