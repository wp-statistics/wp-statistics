<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * First seen filter - filters by first visit date.
 *
 * @since 15.0.0
 */
class FirstSeenFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[first_seen]=... */
    protected $name = 'first_seen';

    /** @var string SQL column: timestamp of visitor's first recorded visit */
    protected $column = 'visitors.first_hit';

    /** @var string Data type: date for date range filtering */
    protected $type = 'date';

    /** @var string UI component: date picker for date selection */
    protected $inputType = 'date';

    /** @var array Supported operators: date range, before date, after date */
    protected $supportedOperators = ['between', 'before', 'after'];

    /** @var array Available on: visitors page for cohort analysis */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('First Seen', 'wp-statistics');
    }
}
