<?php

namespace WP_Statistics\Exception;

use Exception;

class LicenseException extends Exception
{
    private $status;

    public function __construct($message, $status = '', $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);

        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }
}