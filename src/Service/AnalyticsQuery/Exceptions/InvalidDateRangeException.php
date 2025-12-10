<?php

namespace WP_Statistics\Service\AnalyticsQuery\Exceptions;

/**
 * Exception thrown when an invalid date range is provided.
 *
 * @since 15.0.0
 */
class InvalidDateRangeException extends \InvalidArgumentException
{
    /**
     * @param string $message The error message.
     */
    public function __construct(string $message = '')
    {
        parent::__construct(
            $message ?: __('Invalid date range format. Expected YYYY-MM-DD format.', 'wp-statistics')
        );
    }
}
