<?php

namespace WP_Statistics\Service\AnalyticsQuery\Exceptions;

/**
 * Exception thrown when an invalid source is requested.
 *
 * @since 15.0.0
 */
class InvalidSourceException extends \InvalidArgumentException
{
    /**
     * @param string $source The invalid source name.
     */
    public function __construct(string $source)
    {
        parent::__construct(
            sprintf(
                /* translators: %s: Invalid source name */
                __('Unknown source: "%s".', 'wp-statistics'),
                $source
            )
        );
    }
}
