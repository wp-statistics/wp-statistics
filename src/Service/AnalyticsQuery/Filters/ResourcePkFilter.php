<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Resource PK filter - filters by resources table primary key (ID).
 *
 * Unlike ResourceIdFilter which filters by WordPress post ID (resources.resource_id),
 * this filter targets the internal resources.ID primary key. This is needed for
 * non-content pages (home, search, 404, archives) that don't have a WordPress post ID.
 *
 * @since 15.0.0
 */
class ResourcePkFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[resource_pk]=...
     */
    protected $name = 'resource_pk';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: resources.ID (primary key)
     */
    protected $column = 'resources.ID';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer
     */
    protected $type = 'integer';

    /**
     * UI input component type.
     *
     * @var string Input type: searchable
     */
    protected $inputType = 'searchable';

    /**
     * Required base table to enable this filter.
     *
     * @var string|null Table name: views
     */
    protected $requirement = 'views';

    /**
     * Required JOINs to access the resources table.
     *
     * @var array JOIN chain: views -> resource_uris -> resources
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
            'on'    => 'resource_uris.resource_id = resources.ID AND resources.is_deleted = 0',
        ],
    ];

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: views
     */
    protected $groups = ['views', 'referrals'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Resource', 'wp-statistics');
    }
}
