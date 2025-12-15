<?php

namespace WP_Statistics\Service\AnalyticsQuery\Exceptions;

/**
 * Exception thrown when an invalid column is requested.
 *
 * @since 15.0.0
 */
class InvalidColumnException extends \InvalidArgumentException
{
    /**
     * @param string $column The invalid column name.
     */
    public function __construct(string $column)
    {
        parent::__construct(
            sprintf(
                /* translators: %s: Invalid column name */
                __('Unknown column: "%s". Column must be a valid source or group_by field.', 'wp-statistics'),
                $column
            )
        );
    }
}
