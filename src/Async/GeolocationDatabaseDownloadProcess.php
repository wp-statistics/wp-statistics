<?php

namespace WP_Statistics\Async;

use WP_STATISTICS\WP_Background_Process;
use WP_Statistics\Service\Geolocation\GeoServiceProviderInterface;

class GeolocationDatabaseDownloadProcess extends WP_Background_Process
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
        /** @var GeoServiceProviderInterface $provider */
        $provider = $task['provider'];
        $provider->downloadDatabase();

        return false;
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
    }
}
