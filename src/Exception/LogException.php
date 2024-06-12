<?php

namespace WP_Statistics\Exception;

use Exception;

class LogException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        \WP_Statistics::log($this->generateLogMessage($message, $code));
    }

    private function generateLogMessage($message, $code)
    {
        return sprintf(
            "Exception occurred: [Code %d] %s at %s:%d",
            $code,
            $message,
            $this->getFile(),
            $this->getLine()
        );
    }
}
