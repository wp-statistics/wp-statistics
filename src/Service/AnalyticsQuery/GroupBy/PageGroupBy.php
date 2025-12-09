<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Page group by - groups by page/URL.
 *
 * @since 15.0.0
 */
class PageGroupBy extends AbstractGroupBy
{
    protected $name         = 'page';
    protected $column       = 'resource_uris.uri';
    protected $alias        = 'page';
    protected $extraColumns = [
        'resources.cached_title AS page_title',
        'resources.resource_id AS post_id',
        'resources.resource_type AS post_type',
    ];
    protected $joins        = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'views.resource_uri_id = resource_uris.ID',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID',
            'type'  => 'LEFT',
        ],
    ];
    protected $groupBy      = 'resource_uris.ID';
    protected $requirement  = 'views';
}
