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
    protected function getFilePath(string $fileName)
    {
        $uploadDir = wp_upload_dir();
        return $uploadDir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR . '/' . $fileName;
    }

    /**
     * Get the path to the GeoIP database file.
     *
     * @return string
     */
    protected function getDatabasePath()
    {
        return $this->getFilePath($this->databaseFileName);
    }

    /**
     * Delete the existing GeoIP database.
     *
     * @return bool
     */
    public function deleteDatabase()
    {
        if ($this->isDatabaseExist()) {
            $databasePath = $this->getDatabasePath();
            return unlink($databasePath);
        }
        return true; // If the file does not exist, treat it as already deleted
    }

    /**
     * Determine if the Geo-IP database is active.
     *
     * @return bool
     */
    public function isDatabaseExist()
    {
        return file_exists($this->getDatabasePath());
    }

    /**
     * Get the last updated timestamp for the Geolocation database file.
     *
     * @return false|string
     */
    public function getLastDatabaseFileUpdated()
    {
        if ($this->isDatabaseExist()) {
            return date('Y-m-d H:i:s', filemtime($this->getDatabasePath()));
        }
    }

    /**
     * Retrieves the database size for the GeoIP database.
     *
     * @param bool $format Whether to format the size for readability.
     */
    public function getDatabaseSize($format = true)
    {
        if ($this->isDatabaseExist()) {
            if ($format) {
                return size_format(filesize($this->getDatabasePath()));
            } else {
                return filesize($this->getDatabasePath());
            }
        }
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
