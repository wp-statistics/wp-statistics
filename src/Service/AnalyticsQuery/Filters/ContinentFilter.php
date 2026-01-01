<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Continent filter - filters by continent.
 *
 * @since 15.0.0
 */
class ContinentFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[continent]=...
     */
    protected $name = 'continent';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: countries.continent_code
     */
    protected $column = 'countries.continent_code';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> countries
     */
    protected $joins = [
        'table' => 'countries',
        'alias' => 'countries',
        'on'    => 'sessions.country_id = countries.ID',
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
     * @var array Groups: visitors, views
     */
    protected $groups = ['visitors', 'views'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Continent', 'wp-statistics');
    }
}
