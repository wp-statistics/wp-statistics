<?php

namespace WP_Statistics\Service\AnalyticsQuery\Exceptions;

use WP_Statistics\Service\AnalyticsQuery\Registry\GroupByRegistry;

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
        $registry = GroupByRegistry::getInstance();
        parent::__construct(
            sprintf(
                /* translators: 1: Invalid group by name, 2: List of valid group by */
                __('Unknown group_by: "%1$s". Valid group_by: %2$s', 'wp-statistics'),
                $groupBy,
                implode(', ', $registry->getAll())
            )
        );
    }
}
