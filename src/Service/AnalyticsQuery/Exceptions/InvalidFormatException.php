<?php

namespace WP_Statistics\Service\AnalyticsQuery\Exceptions;

/**
 * Exception thrown when an invalid response format is requested.
 *
 * @since 15.0.0
 */
class InvalidFormatException extends \InvalidArgumentException
{
    /**
     * @param string $format The invalid format name.
     */
    public function __construct(string $format)
    {
        parent::__construct(
            sprintf(
                /* translators: %s: Invalid format name */
                __('Invalid format: "%s".', 'wp-statistics'),
                esc_html($format)
            )
        );
    }
}
