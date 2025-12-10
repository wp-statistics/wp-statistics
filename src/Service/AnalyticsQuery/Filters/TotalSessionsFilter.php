<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Total sessions filter - filters by total sessions.
 *
 * @since 15.0.0
 */
class TotalSessionsFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[total_sessions]=... */
    protected $name = 'total_sessions';

    /** @var string SQL column: total session count for the visitor from visitors table */
    protected $column = 'visitors.sessions_count';

    /** @var string Data type: integer for session count comparisons */
    protected $type = 'integer';

    /** @var string UI component: number input for session count entry */
    protected $inputType = 'number';

    /** @var array Supported operators: greater than, less than, and range */
    protected $supportedOperators = ['gt', 'lt', 'between'];

    /** @var array Available on: visitors page for visitor engagement analysis */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Total Sessions', 'wp-statistics');
    }
}
