<?php

namespace WP_Statistics\Service\Logger;

/**
 * Implements logging service for WordPress Statistics.
 */
class LoggerServiceProvider implements LoggerServiceProviderInterface
{
    /**
     * Singleton instance.
     * 
     * @var LoggerServiceProviderInterface|null
     */
    private static $instance = null;

    /**
     * Logger identifier.
     * 
     * @var string
     */
    private $name = '';

    /**
     * Collection of logged errors.
     * 
     * @var array
     */
    private $errors = [];

    /**
     * Map of PHP error constants to severity levels.
     */
    private const ERROR_SEVERITY_MAP = [
        // Critical errors
        E_ERROR => 'critical',
        E_PARSE => 'critical',
        E_CORE_ERROR => 'critical',
        E_COMPILE_ERROR => 'critical',
        E_USER_ERROR => 'critical',
        E_RECOVERABLE_ERROR => 'critical',

        // Standard errors
        E_WARNING => 'error',
        E_CORE_WARNING => 'error',
        E_COMPILE_WARNING => 'error',
        E_USER_WARNING => 'error',

        // Notices
        E_NOTICE => 'notice',
        E_USER_NOTICE => 'notice',
        E_STRICT => 'notice',

        // Deprecation notices
        E_DEPRECATED => 'deprecated',
        E_USER_DEPRECATED => 'deprecated'
    ];

    /**
     * Gets singleton instance of logger service.
     */
    public static function getInstance(): LoggerServiceProviderInterface
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Sets logger identifier.
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets current logger name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Adds single error to collection.
     */
    public function setError(array $error): self
    {
        $errorName = $this->getErrorSeverity($error['type'] ?? E_ERROR);

        $this->errors[] = [
            'date' => date('Y-m-d H:i:s'),
            'name' => $errorName,
            'message' => $error['message'] ?? '',
            'file' => $error['file'] ?? '',
            'line' => $error['line'] ?? 0,
        ];

        return $this;
    }

    /**
     * Sets multiple errors at once.
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Gets all stored errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Maps PHP error number to severity level name.
     *
     * @param int|string $errno
     */
    public function getErrorSeverity($errno): string
    {
        return self::ERROR_SEVERITY_MAP[$errno] ?? 'unknown';
    }

    /**
     * Formats and returns last error as HTML.
     */
    public function print(): string
    {
        if (empty($this->errors)) {
            return '';
        }

        $lastLog = end($this->errors);

        if (!isset($lastLog['name'], $lastLog['message'], $lastLog['date'])) {
            return '';
        }

        $errorType = ucfirst($lastLog['name']);
        $timestamp = strtotime($lastLog['date']);

        $date = sprintf(
            __('%1$s at %2$s', 'wp-statistics'),
            date_i18n('F j, Y', $timestamp),
            date_i18n('H:i:s', $timestamp)
        );

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

    /**
     * Records log message with specified level.
     */
    public function log($message, string $level = 'info'): self
    {
        if (is_array($message)) {
            $message = wp_json_encode($message);
        }

        $log_level = strtoupper($level);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('[%s] [%s]: %s', $this->getName(), $log_level, $message));
        }

        return $this;
    }
}
