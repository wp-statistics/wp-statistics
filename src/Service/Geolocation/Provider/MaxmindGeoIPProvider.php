<?php

namespace WP_Statistics\Service\Geolocation\Provider;

use Exception;
use PharData;
use WP_Error;
use WP_Statistics;
use WP_Statistics\Async\BackgroundProcessFactory;
use WP_STATISTICS\Option;
use WP_Statistics\Dependencies\GeoIp2\Database\Reader;
use WP_Statistics\Service\Geolocation\AbstractGeoIPProvider;

class MaxmindGeoIPProvider extends AbstractGeoIPProvider
{
    /**
     * @var Reader|null
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
    public function fetchGeolocationData(string $ipAddress)
    {
        try {
            // Ensure the reader is initialized
            $this->initializeReader();

            if (empty($this->reader) || !method_exists($this->reader, 'city')) {
                throw new Exception('GeoIP database is corrupted.');
            }

            $record = $this->reader->city($ipAddress);

            return [
                'country'      => $record->country->name,
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

                throw new Exception('GeoIP database not found. Attempting to download...');
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
     * Get the download URL for the Maxmind GeoIP database.
     *
     * @return string
     */
    public function getDownloadUrl()
    {
        $licenseKey = Option::get('geoip_license_key') && Option::get('geoip_license_type') == 'user-license'
            ? Option::get('geoip_license_key')
            : null;

        $defaultUrl = $licenseKey
            ? "https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key={$licenseKey}&suffix=tar.gz"
            : 'https://cdn.jsdelivr.net/npm/geolite2-city/GeoLite2-City.mmdb.gz';

        return $this->getFilteredDownloadUrl($defaultUrl);
    }

    /**
     * Download the GeoIP database, extract it, and handle updates.
     *
     * @return array
     */
    public function downloadDatabase()
    {
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

            $this->extractGzFile($gzFilePath, $dbFile); // Extract the downloaded file
            $this->deleteFile($gzFilePath); // Clean up the temporary file

            // Update options and send notifications
            $this->updateLastDownloadTimestamp();
            $this->batchUpdateIncompleteGeoIp();

            /*
             * @since 14.11.3
             * Email notification is currently disabled because the associated option was removed.
             * However, the sendGeoIpUpdateEmail method is retained here in case future requirements necessitate enabling email notifications via a hook.
             */
            //$this->sendGeoIpUpdateEmail(__('GeoIP Database successfully updated.', 'wp-statistics'));

        } catch (Exception $e) {
            $this->deleteFile($gzFilePath); // Ensure temporary file is deleted in case of an error

            WP_Statistics::log($e->getMessage()); // Log the error for debugging

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

            /**
             * Check if the server is using MaxMind's GeoIP database.
             * If so, extract the database file from the archive.
             */
            if (Option::get('geoip_license_type') === "user-license" && Option::get('geoip_license_key')) {
                if (!class_exists('PharData')) {
                    throw new Exception(__('PharData class not found.', 'wp-statistics'));
                }

                $tarGz         = new PharData($gzFilePath);
                $fileInArchive = trailingslashit($tarGz->current()->getFileName()) . $this->databaseFileName;
                $uploadPath    = dirname($destinationPath);

                // Extract database in the destination path.
                $tarGz->extractTo($uploadPath, $fileInArchive, true);
                $fileExtractedPath = $uploadPath . '/' . $fileInArchive;
                if (!file_exists($fileExtractedPath)) {
                    throw new Exception(esc_html__('Extraction failed: File not found.', 'wp-statistics'));
                }

                if (!copy($fileExtractedPath, $destinationPath)) {
                    throw new Exception(esc_html__('Failed to move extracted file.', 'wp-statistics'));
                }

                // Remove the extracted file and its parent directory
                unlink($fileExtractedPath);
                rmdir(dirname($fileExtractedPath));

                return;
            }

            $gzHandle = gzopen($gzFilePath, 'rb');
            if (!$gzHandle) {
                throw new Exception(__('Failed to open GZ archive.', 'wp-statistics'));
            }

            $dbFileHandle = fopen($destinationPath, 'wb'); // Open the destination file for writing
            if (!$dbFileHandle) {
                gzclose($gzHandle);
                throw new Exception(__('Failed to open destination file for writing.', 'wp-statistics'));
            }

            while (!gzeof($gzHandle)) {
                fwrite($dbFileHandle, gzread($gzHandle, 4096)); // Read from GZ and write to the destination file
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

    /**
     * Retrieves the database type for the GeoIP database.
     *
     * @return string|bool The database type or false on failure.
     */
    public function getDatabaseType()
    {
        $reader = $this->reader;

        if (!$reader) {
            return false;
        }

        return $reader->metadata()->databaseType;
    }

    /**
     * Check the integrity and functionality of the GeoIP database.
     *
     * @return bool|WP_Error True if the database is valid, or WP_Error on failure.
     */
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
            if ($databaseType !== 'GeoLite2-City') {
                throw new Exception(sprintf(__('Unexpected database type %s', 'wp-statistics'), $databaseType));
            }

            return true;

        } catch (Exception $e) {
            return new WP_Error('error', $e->getMessage());
        }
    }
}
