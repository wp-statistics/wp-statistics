<?php

namespace WP_Statistics\Service\Logger;

/**
 * Logger service interface.
 */
interface LoggerServiceProviderInterface
{
    /**
     * Sets logger identifier.
     * 
     * @param string $name
     * @return self
     */
    public function setName($name);

    /**
     * Gets current logger name.
     * 
     * @return string
     */
    public function getName();

    /**
     * Maps PHP error number to severity level name.
     *
     * @param int|string $errno
     * @return string
     */
    public function getErrorSeverity($errno);

    /**
     * Adds single error to collection.
     * 
     * @param array $error
     * @return self
     */
    public function setError($error);

    /**
     * Sets multiple errors at once.
     * 
     * @param array $errors
     * @return self
     */
    public function setErrors($errors);

    /**
     * Gets all stored errors.
     * 
     * @return array
     */
    public function getErrors();
}
