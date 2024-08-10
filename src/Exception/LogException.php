<?php

namespace WP_Statistics\Exception;

use Exception;
use WP_Statistics;

class LogException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        WP_Statistics::log($this->generateLogMessage($message, $code), 'error');
    }

    private function generateLogMessage($message, $code)
    {
        return sprintf(
            __('Exception occurred: [Code %d] %s at %s:%d', 'wp-statistics'),
            $code,
            $message,
            $this->getFile(),
            $this->getLine()
        );
    }
}
