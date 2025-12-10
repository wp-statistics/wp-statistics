<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Author ID filter - filters by post author ID.
 *
 * @since 15.0.0
 */
class AuthorIdFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[author_id]=... */
    protected $name = 'author_id';

    /** @var string SQL column: cached WordPress author ID from resources table */
    protected $column = 'resources.cached_author_id';

    /** @var string Data type: integer for WordPress user IDs */
    protected $type = 'integer';

    /** @var string Required base table: needs views table to access resource data */
    protected $requirement = 'views';

    /** @var array Supported operators: exact match, exclusion, set membership, and numeric comparisons */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];

    /**
     * Required JOINs: views -> resource_uris -> resources chain.
     * Needed to access the cached_author_id stored in the resources table.
     *
     * @var array
     */
    protected $joins = [
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

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Author ID', 'wp-statistics');
    }
}
