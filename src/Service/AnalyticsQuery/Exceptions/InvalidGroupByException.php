<?php

namespace WP_Statistics\Service\AnalyticsQuery\Exceptions;

/**
 * Exception thrown when an invalid group by is requested.
 *
 * @since 15.0.0
 */
class InvalidGroupByException extends \InvalidArgumentException
{
    /**
     * @param string $groupBy The invalid group by name.
     */
    public function __construct(string $groupBy)
    {
        parent::__construct(
            sprintf(
                /* translators: %s: Invalid group by name */
                __('Unknown group_by: "%s".', 'wp-statistics'),
                $groupBy
            )
        );
    }
}
