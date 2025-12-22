<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Search term group by - groups by search query string.
 *
 * Extracts search terms from resource URIs that start with '?s='
 * and groups analytics data by the decoded search term.
 *
 * @since 15.0.0
 */
class SearchTermGroupBy extends AbstractGroupBy
{
    protected $name         = 'search_term';
    // Extract term after /?s= using SUBSTRING_INDEX, then decode spaces
    protected $column       = 'REPLACE(SUBSTRING_INDEX(resource_uris.uri, \'/?s=\', -1), \'+\', \' \')';
    protected $alias        = 'search_term';
    protected $extraColumns = [];
    protected $joins        = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'views.resource_uri_id = resource_uris.ID',
            'type'  => 'INNER',
        ],
    ];
    // Group by the actual search term text to combine duplicate resource_uri records
    protected $groupBy      = 'REPLACE(SUBSTRING_INDEX(resource_uris.uri, \'/?s=\', -1), \'+\', \' \')';
    protected $filter       = 'SUBSTRING(resource_uris.uri, 1, 4) = \'/?s=\'';
    protected $requirement  = 'views';
}
