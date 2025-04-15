<?php

namespace WP_Statistics\Service\Tracking;

use WP_STATISTICS\Service\Tracking\Core\Hits;
use WP_STATISTICS\Service\Tracking\Core\UserOnline;

/**
 * Factory for creating instances of tracking-related services.
 *
 * This central class provides access to reusable service instances such as Hits and UserOnline.
 */
class TrackingFactory
{
    /**
     * Create a new instance of the Hits tracker service.
     *
     * Used for recording visits, REST-based hits, and login/page tracking.
     *
     * @return Hits Instance of the Hits tracking service.
     */
    public static function hits()
    {
        return new Hits();
    }

    /**
     * Create a new instance of the UserOnline tracker service.
     *
     * Used for logging online presence and session activity of visitors.
     *
     * @return UserOnline Instance of the UserOnline tracking service.
     */
    public static function userOnline()
    {
        return new UserOnline();
    }
}
