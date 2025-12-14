<?php

namespace WP_Statistics\Service\AnalyticsQuery\Exceptions;

/**
 * Exception thrown when an invalid operator is used in a filter.
 *
 * @since 15.0.0
 */
class InvalidOperatorException extends \InvalidArgumentException
{
    /**
     * @param string $operator The invalid operator.
     */
    public function __construct(string $operator)
    {
        parent::__construct(
            sprintf(
                /* translators: %s: Invalid operator name */
                __('Invalid operator: "%s".', 'wp-statistics'),
                $operator
            )
        );
    }
}
