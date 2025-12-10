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
                __('Invalid operator: "%s". Valid operators: is, is_not, in, not_in, contains, starts_with, ends_with, gt, gte, lt, lte', 'wp-statistics'),
                $operator
            )
        );
    }
}
