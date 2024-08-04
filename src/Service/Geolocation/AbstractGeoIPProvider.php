<?php

namespace WP_Statistics\Service\Geolocation\Provider;

use WP_STATISTICS\Option;
use WP_Statistics\Async\BackgroundProcessFactory;
use WP_Statistics\Helper;

abstract class AbstractGeoIPProvider implements GeoServiceProviderInterface
{
    /**
     * @var string
     */
    protected $databaseFileName = '';

    /**
     * Construct the full path for a given file name in the uploads directory.
     *
     * @param string $fileName
     * @return string
     */
    protected function getFilePath(string $fileName): string
    {
        $uploadDir = wp_upload_dir();
        return $uploadDir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR . '/' . $fileName;
    }

    /**
     * Get the path to the GeoIP database file.
     *
     * @return string
     */
    protected function getDatabasePath(): string
    {
        return $this->getFilePath($this->databaseFileName);
    }

    /**
     * Delete the existing GeoIP database.
     *
     * @return bool
     */
    public function deleteDatabase(): bool
    {
        $databasePath = $this->getDatabasePath();
        if (file_exists($databasePath)) {
            return unlink($databasePath);
        }
        return true; // If the file does not exist, treat it as already deleted
    }

    /**
     * Get information about the GeoIP database file.
     *
     * @return array|null
     */
    public function getDatabaseFileInfo(): ?array
    {
        $databasePath = $this->getDatabasePath();
        if (file_exists($databasePath)) {
            $sizeInBytes     = filesize($databasePath);
            $sizeInMegabytes = $sizeInBytes / (1024 * 1024); // Convert bytes to megabytes

            return [
                'size'          => round($sizeInMegabytes, 2) . ' MB', // File size in megabytes
                'last_modified' => date('Y-m-d H:i:s', filemtime($databasePath)), // Last modified timestamp formatted
            ];
        }
        return null; // Return null if the file does not exist
    }

    /**
     * Update the last download timestamp.
     */
    protected function updateLastDownloadTimestamp()
    {
        Option::update('last_geoip_dl', time());

        // Update last download timestamp after successful completion
        //update_option('wp_statistics_geo_db_last_download', time()); @todo
    }

    /**
     * Batch update incomplete GeoIP info for visitors.
     */
    protected function batchUpdateIncompleteGeoIp()
    {
        if (Option::get('auto_pop')) {
            BackgroundProcessFactory::batchUpdateIncompleteGeoIpForVisitors();
        }
    }

    /**
     * Send email notification about the GeoIP update.
     *
     * @param string $notice
     */
    protected function sendGeoIpUpdateEmail(string $notice)
    {
        if (Option::get('geoip_report')) {
            Helper::send_mail(
                Option::getEmailNotification(),
                __('GeoIP update on', 'wp-statistics') . ' ' . get_bloginfo('name'),
                $notice,
                true,
                [
                    "email_title" => __('GeoIP update on', 'wp-statistics') . ' <a href="' . get_bloginfo('url') . '" target="_blank" style="text-decoration: none; color: #303032; font-family: Roboto,Arial,Helvetica,sans-serif; font-size: 16px; font-weight: 600; line-height: 18.75px;font-style: italic">' . get_bloginfo('name') . '</a>'
                ]
            );
        }
    }
}
