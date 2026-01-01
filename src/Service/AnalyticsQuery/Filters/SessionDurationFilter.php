<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Session duration filter - filters by session length in seconds.
 *
 * @since 15.0.0
 */
class SessionDurationFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[session_duration]=...
     */
    protected $name = 'session_duration';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: sessions.duration
     */
    protected $column = 'sessions.duration';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer (seconds)
     */
    protected $type = 'integer';

    /**
     * UI input component type.
     *
     * @var string Input type: number
     */
    protected $inputType = 'number';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: gt, lt, between
     */
    protected $supportedOperators = ['gt', 'lt', 'between'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors', 'views'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Session Duration', 'wp-statistics');
    }
}
