<?php

namespace WP_Statistics\Async;

use WP_STATISTICS\GeoIP;
use WP_STATISTICS\WP_Async_Request;

class GeoIPDatabaseDownloadProcess extends WP_Async_Request
{
    /**
     * @var string
     */
    protected $action = 'geoip_database_download';

    /**
     * Handle a dispatched request.
     *
     * Override this method to perform any actions required
     * during the async request.
     */
    protected function handle()
    {
        GeoIP::download();
    }
}
