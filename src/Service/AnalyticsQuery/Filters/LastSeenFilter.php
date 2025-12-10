<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Last seen filter - filters by last activity timestamp.
 *
 * @since 15.0.0
 */
class LastSeenFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[last_seen]=... */
    protected $name = 'last_seen';

    /** @var string SQL column: timestamp of visitor's most recent activity */
    protected $column = 'visitors.last_hit';

    /** @var string Data type: date for date range filtering */
    protected $type = 'date';

    /** @var string UI component: date picker for date selection */
    protected $inputType = 'date';

    /** @var array Supported operators: relative time (in_the_last), date range, before/after */
    protected $supportedOperators = ['in_the_last', 'between', 'before', 'after'];

    /** @var array Available on: visitors page for activity recency analysis */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Last Seen', 'wp-statistics');
    }
}
