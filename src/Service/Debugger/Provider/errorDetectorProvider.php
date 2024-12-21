<?php

namespace WP_Statistics\Service\Debugger\Provider;

use WP_Statistics\Option;
use WP_Statistics\Service\Debugger\AbstractDebuggerProvider;

class ErrorDetectorProvider extends AbstractDebuggerProvider
{
    const ERROR_LOG_OPTION = 'wp_statistics_error_log';

    /**
     * Stores the cached error logs
     * 
     * @var array
     */
    private array $errors;

    /**
     * Captures and stores the last occurred PHP error
     * 
     * Records error details including date, type, message, file, and line number
     * Maintains a maximum of 10 entries in the log using FIFO (First In, First Out)
     * 
     * @return void
     */
    public function setError()
    {
        $error = error_get_last();
        
        if (empty($error)) {
           return; 
        }

        $errorName = $this->getErrorSeverity($error['type']);
        $errorLog  = Option::get(self::ERROR_LOG_OPTION, []);
        
        if (count($errorLog) >= 10) {
            array_shift($errorLog);
        }

        $errorLog[] = [
            'date' => date('Y-m-d H:i:s'),
            'name' => $errorName,
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
        ];

        Option::update(self::ERROR_LOG_OPTION, $errorLog);
    }

    /**
     * Get the error name based on the error number
     *
     * @param int $errno The error number
     * @return string The error name
     */
     private function getErrorSeverity($errno)
    {
        switch ($errno) {
            // Critical errors
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                return 'critical';

            // Standard errors
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return 'error';

            // Notices
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
                return 'notice';

            // Deprecation notices
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return 'deprecated';

            default:
                return 'unknown';
        }
    }

    /**
     * Get the error log from the option
     *
     * @return array The error log
     */
    public function getErrors(): array
    {
        if (empty($this->errors)) {
            $this->errors = Option::get(self::ERROR_LOG_OPTION, []);
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
    public function printLogs() {
        if (empty($this->errors)) {
            return;
        }
        
        $output  = '';
        $lastLog = end($this->errors);

        if (!isset($lastLog['name'], $lastLog['message'], $lastLog['date'])) {
            return;
        }

        $errorType = ucfirst($lastLog['name']);

        // Format date: "December 20, 2024 at 10:37:44"
        $timestamp = strtotime($lastLog['date']);

        $date = sprintf(
            __('%1$s at %2$s', 'wp-statistics'),
            date_i18n('F j, Y', $timestamp),
            date_i18n('H:i:s', $timestamp)
        );
    
        // Format message: trim extra spaces and newlines
        $message = trim(preg_replace('/\s+/', ' ', $lastLog['message']));
    
        return sprintf(
            '<p>%1$s %2$s</p>
            <p>%3$s %4$s</p>
            <p>%5$s %6$s</p>',
            esc_html__('Type:', 'wp-statistics'),
            esc_html($errorType),
            esc_html__('Message:', 'wp-statistics'),
            esc_html($message),
            esc_html__('Occurred At:', 'wp-statistics'),
            esc_html($date)
        );
    }
}