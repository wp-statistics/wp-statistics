<?php

namespace WP_Statistics\Async;

use WP_Filesystem_Base;

class GeolocationDatabaseDownloadProcess extends \WP_Background_Process
{
    /**
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * @var string
     */
    protected $action = 'geolocation_database_download';

    /**
     * Task: Download the geolocation database.
     *
     * @param mixed $task Database URL and destination path
     * @return false
     */
    protected function task($task)
    {
        $url         = $task['url'];
        $destination = $task['destination'];

        $response = wp_remote_get($url, ['timeout' => 120]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);

        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }

        if ($wp_filesystem instanceof WP_Filesystem_Base && $wp_filesystem->put_contents($destination, $body)) {
            $this->extractDatabase($destination);
        }

        return false;
    }

    /**
     * Extract the downloaded database archive.
     *
     * @param string $archivePath
     */
    protected function extractDatabase($archivePath)
    {
        $phar = new \PharData($archivePath);
        $phar->decompress(); // .tar
        $phar->extractTo(dirname($archivePath), null, true);
    }

    /**
     * Complete processing.
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete()
    {
        parent::complete();

        // Update last download timestamp after successful completion
        update_option('wp_statistics_geo_db_last_download', time());
    }
}
