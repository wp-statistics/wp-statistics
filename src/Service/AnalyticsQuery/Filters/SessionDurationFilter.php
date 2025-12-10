<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Session duration filter - filters by session length in seconds.
 *
 * @since 15.0.0
 */
class SessionDurationFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[session_duration]=... */
    protected $name = 'session_duration';

    /** @var string SQL column: session duration in seconds from sessions table */
    protected $column = 'sessions.duration';

    /** @var string Data type: integer for duration comparison in seconds */
    protected $type = 'integer';

    /** @var string UI component: number input for duration entry in seconds */
    protected $inputType = 'number';

    /** @var array Supported operators: greater than, less than, and range */
    protected $supportedOperators = ['gt', 'lt', 'between'];

    /** @var array Available on: visitors page for engagement analysis */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Session Duration', 'wp-statistics');
    }
}
