<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Group by event page (the page where the event occurred).
 *
 * Joins events.resource_uri_id → resource_uris → resources to get
 * page information. Similar to PageGroupBy but for events table.
 *
 * @since 15.0.0
 */
class EventPageGroupBy extends AbstractGroupBy
{
    protected $name         = 'event_page';
    protected $column       = 'resource_uris.uri';
    protected $alias        = 'page_uri';
    protected $extraColumns = [
        'resource_uris.ID AS page_uri_id',
        'resources.ID AS resource_id',
        'resources.cached_title AS page_title',
        'resources.resource_id AS page_wp_id',
        'resources.resource_type AS page_type',
    ];
    protected $joins        = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'events.resource_uri_id = resource_uris.ID',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID AND resources.is_deleted = 0',
            'type'  => 'LEFT',
        ],
    ];
    protected $groupBy      = 'resource_uris.ID';
    protected $requirement  = 'events';
}
