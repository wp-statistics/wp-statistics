<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Author ID filter - filters by post author ID.
 *
 * @since 15.0.0
 */
class AuthorIdFilter extends AbstractFilter
{
    protected $name               = 'author_id';
    protected $column             = 'resources.cached_author_id';
    protected $type               = 'integer';
    protected $requirement        = 'views';
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];
    protected $joins              = [
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
