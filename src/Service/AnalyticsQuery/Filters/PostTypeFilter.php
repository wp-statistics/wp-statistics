<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Post type filter - filters by resource/post type.
 *
 * @since 15.0.0
 */
class PostTypeFilter extends AbstractFilter
{
    protected $name        = 'post_type';
    protected $column      = 'resources.resource_type';
    protected $type        = 'string';
    protected $requirement = 'views';
    protected $joins       = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'views.resource_uri_id = resource_uris.ID',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID',
        ],
    ];
}
