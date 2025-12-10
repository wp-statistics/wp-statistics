<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * City filter - filters by city name.
 *
 * @since 15.0.0
 */
class CityFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[city]=... */
    protected $name = 'city';

    /** @var string SQL column: city name from cities lookup table (e.g., New York, London, Tokyo) */
    protected $column = 'cities.city_name';

    /** @var string Data type: string for city name matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> cities.
     * Links session's city ID to the city details lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'cities',
        'alias' => 'cities',
        'on'    => 'sessions.city_id = cities.ID',
    ];

    /** @var string UI component: searchable autocomplete for large city list */
    protected $inputType = 'searchable';

    /** @var array Supported operators: exact match and exclusion */
    protected $supportedOperators = ['is', 'is_not'];

    /** @var array Available on: visitors page for geographic analysis */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('City', 'wp-statistics');
    }

    /**
     * Search city options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_cities';

        $sql = "SELECT city_name as value, city_name as label FROM {$table}";

        if (!empty($search)) {
            $sql .= $wpdb->prepare(" WHERE city_name LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        $sql .= " ORDER BY city_name ASC LIMIT %d";
        $sql  = $wpdb->prepare($sql, $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results ?: [];
    }
}
