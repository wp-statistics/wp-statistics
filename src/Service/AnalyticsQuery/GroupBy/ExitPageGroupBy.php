<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Exit page group by - groups by the last page visited in a session (exit page).
 *
 * This groups sessions by their final page (where the session ended),
 * as opposed to PageGroupBy which groups by all page views.
 *
 * @since 15.0.0
 */
class ExitPageGroupBy extends AbstractGroupBy
{
    protected $name         = 'exit_page';
    protected $column       = 'exit_page_uris.uri';
    protected $alias        = 'page_uri';
    protected $extraColumns = [
        'exit_page_uris.ID AS page_uri_id',
        'exit_page_resources.ID AS resource_id',
        'exit_page_resources.cached_title AS page_title',
        'exit_page_resources.resource_id AS page_wp_id',
        'exit_page_resources.resource_type AS page_type',
    ];
    protected $joins        = [
        [
            'table' => 'views',
            'alias' => 'exit_views',
            'on'    => 'sessions.last_view_id = exit_views.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resource_uris',
            'alias' => 'exit_page_uris',
            'on'    => 'exit_views.resource_uri_id = exit_page_uris.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resources',
            'alias' => 'exit_page_resources',
            'on'    => 'exit_page_uris.resource_id = exit_page_resources.ID',
            'type'  => 'LEFT',
        ],
    ];
    protected $groupBy      = 'exit_page_uris.ID';
    protected $requirement  = 'sessions';
}
