<?php
/**
 * Global functions for WP Statistics.
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

use WP_Statistics\Bootstrap;
use WP_Statistics\Service\Logger\LoggerFactory;

if (!function_exists('WP_Statistics')) {
    /**
     * Global function to get WP Statistics instance.
     *
     * Returns a compatibility object for backward compatibility.
     *
     * @return object
     */
    function WP_Statistics()
    {
        return new class {
            public function getBackgroundProcess($key)
            {
                return Bootstrap::getBackgroundProcess($key);
            }

            public function log($message, $level = 'info')
            {
                LoggerFactory::logger('file')->log($message, $level);
            }
        };
    }
}
