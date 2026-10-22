<?php

namespace WP_Statistics\Service\Debugger\Provider;

use WP_Statistics\Option;
use WP_Statistics\Service\Debugger\AbstractDebuggerProvider;
use WP_Statistics\Service\Logger\LoggerFactory;
use WP_Statistics\Service\Logger\LoggerServiceProvider;

/**
 * Error Detector Provider Class
 *
 * This class provides functionality for detecting, logging, and managing PHP errors.
 * It extends the AbstractDebuggerProvider and implements error tracking with a FIFO
 * (First In, First Out) system that maintains up to 10 recent error entries.
 * The class uses WordPress options to persistently store error logs.
 */
class ErrorsDetectorProvider extends AbstractDebuggerProvider
{
    const ERROR_LOG_OPTION = 'tracker_js_errors';

    /**
     * Stores the cached error logs.
     *
     * @var array
     */
    private $errors;

    /**
     * Captures and stores the last occurred PHP error
     *
     * Records error details including date, type, message, file, and line number
     * Maintains a maximum of 10 entries in the log using FIFO (First In, First Out)
     *
     * @return void
     */
    public function errorListener()
    {
        $disableLogs = apply_filters('wp_statistics_disable_tracker_debugger_logs', false);

        if ($disableLogs) {
            return;
        }

        $error = error_get_last();
        if (empty($error)) {
            return;
        }

        if (!$this->isOwnError($error)) {
            return;
        }

        // Clear only errors handled by this listener. Other code may still need
        // to inspect errors raised by a different plugin or the active theme.
        error_clear_last();

        $errorName   = LoggerFactory::logger('tracker')->getErrorSeverity($error['type']);
        $currentLogs = Option::getOptionGroup(self::ERROR_LOG_OPTION, null, []);
        $count       = count($currentLogs);

        if ($count >= 10) {
            $currentLogs = array_slice($currentLogs, -9, null, true);
            Option::updateGroupOptions(self::ERROR_LOG_OPTION, $currentLogs);
        }

        $index = 0;
        if ($count > 0) {
            $index = array_key_last($currentLogs);
            ++$index;
        }

        $errorMessage     = !empty($error['message']) ? $error['message'] : '';
        $maxMessageLength = apply_filters('wp_statistics_tracker_debugger_log_message_max_length', 1000);

        if (strlen($errorMessage) > $maxMessageLength) {
            $truncatedMessage = substr($errorMessage, 0, $maxMessageLength);
            $truncatedMessage .= '...';

            $errorMessage = $truncatedMessage;
        }

        Option::saveOptionGroup(
            $index,
            [
                'date'    => gmdate('Y-m-d H:i:s'),
                'name'    => $errorName,
                'message' => $errorMessage,
                'file'    => $error['file'],
                'line'    => $error['line'],
            ],
            self::ERROR_LOG_OPTION
        );
    }

    /**
     * Whether an error was raised by WP Statistics or one of its add-ons.
     *
     * error_get_last() returns the last error of the whole request, whoever raised it. A
     * theme or another plugin printing a deprecation (for example Kirki on PHP 8.2+) was
     * shown in the debugger as "PHP Errors Detected in tracker.js", which sent site owners
     * looking for a fault in the tracker that was not there (#1170).
     *
     * @param array $error The array returned by error_get_last().
     * @return bool
     */
    private function isOwnError($error)
    {
        $file = isset($error['file']) ? wp_normalize_path((string) $error['file']) : '';
        $own  = false;

        if ($file !== '') {
            // The plugin itself, or an add-on folder such as wp-statistics-data-plus. Each
            // folder is compared as written and as resolved: PHP reports the real path, and a
            // symlinked plugins folder would otherwise never match.
            $prefixes = [];
            foreach ([WP_STATISTICS_DIR => '', WP_PLUGIN_DIR => '/wp-statistics-'] as $dir => $suffix) {
                foreach (array_unique([$dir, (string) realpath($dir)]) as $path) {
                    if ($path !== '') {
                        $prefixes[] = untrailingslashit(wp_normalize_path($path)) . ($suffix === '' ? '/' : $suffix);
                    }
                }
            }

            foreach ($prefixes as $prefix) {
                if (strpos($file, $prefix) === 0) {
                    $own = true;
                    break;
                }
            }
        }

        /**
         * Filters whether an error is logged in the tracker debugger.
         *
         * @param bool  $own   Whether the error was raised inside WP Statistics or an add-on.
         * @param array $error The error from error_get_last().
         */
        return (bool) apply_filters('wp_statistics_tracker_debugger_log_error', $own, $error);
    }

    /**
     * Get the error log from the option
     *
     * @return array The error log
     */
    public function getErrors()
    {
        if (empty($this->errors)) {
            $this->errors = Option::getOptionGroup(self::ERROR_LOG_OPTION, null, []);
        }
        return $this->errors;
    }

    /**
     * Formats and outputs the most recent error log entry as HTML
     *
     * Displays error type, message, and timestamp of the last recorded error
     * Returns empty if no errors are logged
     *
     * @return string|void HTML formatted error information or void if no errors exist
     */
    public function printError()
    {
        return LoggerFactory::logger('tracker')
            ->setErrors($this->errors)
            ->print();
    }
}
