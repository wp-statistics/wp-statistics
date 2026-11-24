<?php

namespace WP_Statistics\Exception;

use Exception;
use Throwable;

class SystemErrorException extends Exception
{
    public function __construct($message, $code = 0, ?Throwable $previous = null)
    {
        /* translators: %s: string value */
        $message = sprintf(__('System error: %s', 'wp-statistics'), $message);
        parent::__construct($message, $code, $previous);
    }
}