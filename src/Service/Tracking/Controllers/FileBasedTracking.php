<?php

namespace WP_Statistics\Trackers\Controllers;

use WP_Statistics\Abstracts\BaseTrackerController;

/**
 * File-Based Tracking Controller
 *
 * A minimal implementation of the BaseTrackerController for file-based tracking.
 * This controller provides a foundation for tracking implementations that rely on
 * direct file system operations rather than HTTP requests. Currently serves as
 * a placeholder for future file-based tracking capabilities.
 *
 * @since 15.0.0
 */
class FileBasedTracking extends BaseTrackerController
{
    /**
     * Register tracking functionality.
     * Currently implemented as a no-op placeholder for future file-based tracking logic.
     *
     * @return void
     * @since 15.0.0
     * @todo Implement file system monitoring and data collection:
     *       - Set up file watchers for tracking data
     *       - Configure data storage location and format
     *       - Add error handling for file operations
     *
     */
    public function register()
    {
    }

    /**
     * Get the tracking route.
     * Returns an empty string as file-based tracking doesn't use HTTP routes.
     *
     * @return string Empty string as no route is needed for file-based tracking
     * @since 15.0.0
     * @todo Consider implementing a virtual route for internal tracking purposes
     *       or maintain as is if file-only tracking is sufficient
     *
     */
    public function getRoute()
    {
        return '';
    }
}
