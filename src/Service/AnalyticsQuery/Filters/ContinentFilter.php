<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Continent filter - filters by continent code.
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
     * UI input component type.
     *
     * @var string Input type: dropdown
     */
    protected $inputType = 'dropdown';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors', 'views', 'individual-content', 'referrals'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Continent', 'wp-statistics');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): ?array
    {
        return [
            ['value' => 'AF', 'label' => esc_html__('Africa', 'wp-statistics')],
            ['value' => 'AN', 'label' => esc_html__('Antarctica', 'wp-statistics')],
            ['value' => 'AS', 'label' => esc_html__('Asia', 'wp-statistics')],
            ['value' => 'EU', 'label' => esc_html__('Europe', 'wp-statistics')],
            ['value' => 'NA', 'label' => esc_html__('North America', 'wp-statistics')],
            ['value' => 'OC', 'label' => esc_html__('Oceania', 'wp-statistics')],
            ['value' => 'SA', 'label' => esc_html__('South America', 'wp-statistics')],
        ];
    }
}
