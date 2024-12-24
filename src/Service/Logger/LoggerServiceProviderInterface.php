<?php

namespace WP_Statistics\Service\Logger;

/**
 * Logger service interface for WordPress Statistics.
 */
interface LoggerServiceProviderInterface
{
    /**
     * Sets logger identifier.
     */
    public function setName(string $name): self;

    /**
     * Gets current logger name.
     */
    public function getName(): string;

    /**
     * Maps PHP error number to severity level name.
     * 
     * @param int|string $errno
     */
    public function getErrorSeverity($errno): string;

    /**
     * Formats and returns last error as HTML.
     */
    public function print(): string;

    /**
     * Records log message with specified level.
     * 
     * @param string|array $message
     */
    public function log($message, string $level = 'info'): self;

    /**
     * Adds single error to collection.
     */
    public function setError(array $error): self;

    /**
     * Sets multiple errors at once.
     */
    public function setErrors(array $errors): self;

    /**
     * Gets all stored errors.
     */
    public function getErrors(): array;
}
