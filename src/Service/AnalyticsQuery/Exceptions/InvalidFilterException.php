<?php

namespace WP_Statistics\Service\AnalyticsQuery\Exceptions;

/**
 * Exception thrown when an invalid filter is requested.
 *
 * @since 15.0.0
 */
class InvalidFilterException extends \InvalidArgumentException
{
    /**
     * @param string $filter The invalid filter name.
     */
    public function __construct(string $filter)
    {
        parent::__construct(
            sprintf(
                __('Invalid filter: "%s".', 'wp-statistics'),
                $filter
            )
        );
    }
}
