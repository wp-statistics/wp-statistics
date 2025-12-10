<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Continent filter - filters by continent.
 *
 * @since 15.0.0
 */
class ContinentFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[continent]=... */
    protected $name = 'continent';

    /** @var string SQL column: continent code from countries table (e.g., NA, EU, AS, AF, OC, SA, AN) */
    protected $column = 'countries.continent_code';

    /** @var string Data type: string for continent code matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> countries.
     * Links session's country ID to get the continent code.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'countries',
        'alias' => 'countries',
        'on'    => 'sessions.country_id = countries.ID',
    ];

    /** @var array Supported operators: exact match, exclusion, and set membership */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Continent', 'wp-statistics');
    }
}
