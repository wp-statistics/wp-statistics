<?php
/**
 * Global functions for WP Statistics v15.
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

if (!function_exists('WP_Statistics')) {
    /**
     * Global function to get WP Statistics instance.
     *
     * Works with both v14 and v15 architectures.
     * In v15 mode, returns a compatibility object.
     * In v14 mode, returns the legacy WP_Statistics singleton.
     *
     * @return object
     */
    function WP_Statistics()
    {
        if (\WP_Statistics\Bootstrap::isV15()) {
            return new class {
                public function getBackgroundProcess($key)
                {
                    return \WP_Statistics\Bootstrap::getBackgroundProcess($key);
                }

                public function log($message, $level = 'info')
                {
                    \WP_Statistics\Bootstrap::log($message, $level);
                }
            };
        }

        return \WP_Statistics::instance();
    }
}
