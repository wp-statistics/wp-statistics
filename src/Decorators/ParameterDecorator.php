<?php

namespace WP_Statistics\Decorators;

/**
 * Decorator for a record from the 'parameters' table.
 *
 * Provides accessors for each column in the record.
 */
class ParameterDecorator
{
    /**
     * The parameter record.
     *
     * @var object|null
     */
    private $parameter;

    /**
     * ParameterDecorator Constructor.
     *
     * @param object|null $parameter Record from the 'parameters' table or null.
     */
    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * Get the record ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return empty($this->parameter->ID) ? null : (int)$this->parameter->ID;
    }

    /**
     * Get the session ID.
     *
     * @return int|null
     */
    public function getSessionId()
    {
        return empty($this->parameter->session_id) ? null : (int)$this->parameter->session_id;
    }

    /**
     * Get the resource ID.
     *
     * @return int|null
     */
    public function getResourceId()
    {
        return empty($this->parameter->resource_id) ? null : (int)$this->parameter->resource_id;
    }

    /**
     * Get the view ID.
     *
     * @return int|null
     */
    public function getViewId()
    {
        return empty($this->parameter->view_id) ? null : (int)$this->parameter->view_id;
    }

    /**
     * Get the parameter name.
     *
     * @return string|null
     */
    public function getParameter()
    {
        return empty($this->parameter->parameter) ? null : (string)$this->parameter->parameter;
    }

    /**
     * Get the parameter value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return empty($this->parameter->value) ? null : (string)$this->parameter->value;
    }

    /**
     * Get the full parameter string in "key=value" format.
     *
     * Combines the parameter name and its value as a query string pair.
     *
     * @return string The full parameter string or an empty string if the key is missing.
     */
    public function getFull()
    {
        $key   = $this->getParameter();
        $value = $this->getValue();

        if ($key === null) {
            return '';
        }

        return $value !== null ? "{$key}={$value}" : $key;
    }
}
