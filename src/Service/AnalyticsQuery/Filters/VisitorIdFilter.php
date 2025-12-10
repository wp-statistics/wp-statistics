<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Visitor ID filter - filters by visitor ID.
 *
 * @since 15.0.0
 */
class VisitorIdFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[visitor_id]=... */
    protected $name = 'visitor_id';

    /** @var string SQL column: visitor ID foreign key from sessions table */
    protected $column = 'sessions.visitor_id';

    /** @var string Data type: integer for visitor ID matching */
    protected $type = 'integer';

    /** @var array Supported operators: exact match, exclusion, set membership, and numeric comparisons */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Visitor ID', 'wp-statistics');
    }
}
