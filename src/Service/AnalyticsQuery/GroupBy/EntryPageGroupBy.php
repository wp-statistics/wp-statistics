<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Entry page group by - groups by the first page visited in a session (entry page).
 *
 * This groups sessions by their initial page (where the session started),
 * as opposed to PageGroupBy which groups by all page views.
 *
 * @since 15.0.0
 */
class EntryPageGroupBy extends AbstractGroupBy
{
    protected $name         = 'entry_page';
    protected $column       = 'entry_page_uris.uri';
    protected $alias        = 'page_uri';
    protected $extraColumns = [
        'entry_page_uris.ID AS page_uri_id',
        'entry_page_resources.ID AS resource_id',
        'entry_page_resources.cached_title AS page_title',
        'entry_page_resources.resource_id AS page_wp_id',
        'entry_page_resources.resource_type AS page_type',
    ];
    protected $joins        = [
        [
            'table' => 'views',
            'alias' => 'entry_views',
            'on'    => 'sessions.initial_view_id = entry_views.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resource_uris',
            'alias' => 'entry_page_uris',
            'on'    => 'entry_views.resource_uri_id = entry_page_uris.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resources',
            'alias' => 'entry_page_resources',
            'on'    => 'entry_page_uris.resource_id = entry_page_resources.ID',
            'type'  => 'LEFT',
        ],
    ];
    protected $groupBy      = 'entry_page_uris.ID';
    protected $requirement  = 'sessions';
}
