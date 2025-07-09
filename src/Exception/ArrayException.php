<?php

namespace WP_Statistics\Exception;

use Exception;

/**
 * Class ArrayException
 *
 * Custom exception that accepts and returns an array of data.
 */
class ArrayException extends Exception
{
    /**
     * @var array The original data passed to the exception.
     */
    protected $data;

    /**
     * ArrayException constructor.
     *
     * @param array $data The data to be stored in the exception.
     * @param int $code The HTTP or application-specific error code.
     * @param Exception|null $previous The previous exception for nested exceptions.
     */
    public function __construct($data, $code = 0, Exception $previous = null)
    {
        $this->data = $data;
        parent::__construct(json_encode($data), $code, $previous);
    }

    /**
     * Get the original array data passed to the exception.
     *
     * @return array The array of data.
     */
    public function getData()
    {
        return $this->data;
    }
}
