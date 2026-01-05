<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Event name filter - filters by event name.
 *
 * @since 15.0.0
 */
class EventNameFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[event_name]=...
     */
    protected $name = 'event_name';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: events.event_name
     */
    protected $column = 'events.event_name';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * Required base table for this filter.
     *
     * @var string|null
     */
    protected $requirement = 'events';

    /**
     * UI input component type.
     *
     * @var string Input type: text
     */
    protected $inputType = 'text';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: events
     */
    protected $groups = ['events'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Event Name', 'wp-statistics');
    }
}
