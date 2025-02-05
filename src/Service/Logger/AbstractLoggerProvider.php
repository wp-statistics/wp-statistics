<?php

namespace WP_Statistics\Service\Logger;

/**
 * Abstract base class for logger service.
 */
abstract class AbstractLoggerProvider implements LoggerServiceProviderInterface
{
    /**
     * Logger identifier.
     * 
     * @var string
     */
    protected $name = '';

    /**
     * Collection of logged errors.
     * 
     * @var array
     */
    protected $errors = [];

    /**
     * Map of PHP error constants to severity levels.
     */
    protected static $ERROR_SEVERITY_MAP = [
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
     * Sets logger identifier.
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets current logger name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Adds single error to collection.
     */
    public function setError($error)
    {
        $errorName = $this->getErrorSeverity(isset($error['type']) ? $error['type'] : E_ERROR);

        $this->errors[] = [
            'date' => date('Y-m-d H:i:s'),
            'name' => $errorName,
            'message' => isset($error['message']) ? $error['message'] : '',
            'file' => isset($error['file']) ? $error['file'] : '',
            'line' => isset($error['line']) ? $error['line'] : 0,
        ];

        return $this;
    }

    /**
     * Sets multiple errors at once.
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Gets all stored errors.
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Maps PHP error number to severity level name.
     *
     * @param int|string $errno
     */
    public function getErrorSeverity($errno)
    {
        return isset(self::$ERROR_SEVERITY_MAP[$errno]) ? self::$ERROR_SEVERITY_MAP[$errno] : 'unknown';
    }
}
