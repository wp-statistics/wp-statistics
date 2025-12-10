<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Resource ID filter - filters by resource ID.
 *
 * @since 15.0.0
 */
class ResourceIdFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[resource_id]=... */
    protected $name = 'resource_id';

    /** @var string SQL column: WordPress post/resource ID from views table */
    protected $column = 'views.resource_id';

    /** @var string Data type: integer for WordPress post ID matching */
    protected $type = 'integer';

    /** @var string Required base table: needs views table to access resource IDs */
    protected $requirement = 'views';

    /** @var array Supported operators: exact match, exclusion, set membership, and numeric comparisons */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Resource ID', 'wp-statistics');
    }
}
