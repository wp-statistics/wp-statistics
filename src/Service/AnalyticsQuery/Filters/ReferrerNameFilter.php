<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer name filter - filters by referrer name (e.g., Google, Facebook).
 *
 * @since 15.0.0
 */
class ReferrerNameFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[referrer_name]=...
     */
    protected $name = 'referrer_name';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: referrers.name
     */
    protected $column = 'referrers.name';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> referrers
     */
    protected $joins = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in, contains, starts_with, ends_with
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors, views
     */
    protected $groups = ['visitors', 'views'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Referrer Name', 'wp-statistics');
    }
}
