<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Event page ID filter - filters events by page ID.
 *
 * @since 15.0.0
 */
class EventPageIdFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[event_page_id]=...
     */
    protected $name = 'event_page_id';

    /**
     * SQL column for WHERE clause.
     *
     * Filters by WordPress post ID via the resource join chain:
     * events.resource_uri_id → resource_uris → resources.resource_id
     *
     * @var string Column path: resources.resource_id (WordPress post ID)
     */
    protected $column = 'resources.resource_id';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer
     */
    protected $type = 'integer';

    /**
     * Required base table for this filter.
     *
     * @var string|null
     */
    protected $requirement = 'events';

    /**
     * Required JOINs to access the resources table from events.
     *
     * @var array JOIN chain: events → resource_uris → resources
     */
    protected $joins = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'events.resource_uri_id = resource_uris.ID',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID AND resources.is_deleted = 0',
        ],
    ];

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
        return esc_html__('Event Page ID', 'wp-statistics');
    }
}
