<?php

namespace WP_Statistics\Service\Logger\Provider;

use WP_Statistics\Service\Logger\AbstractLoggerProvider;

/**
 * File-based logger provider implementation.
 */
class FileProvider extends AbstractLoggerProvider
{
    /**
     * Logs a message to the error log.
     * 
     * @param string|array $message The message to log.
     * @param string $level The log level (default is 'info').
     */
    public function log($message, $level = 'info')
    {
        if (is_array($message)) {
            $message = wp_json_encode($message);
        }

        $log_level = strtoupper($level);

        // Log the message if WP_DEBUG is enabled.
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('[WP STATISTICS] [%s]: %s', $log_level, $message));
        }
    }
}
