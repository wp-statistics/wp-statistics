<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * City filter - filters by city name.
 *
 * @since 15.0.0
 */
class CityFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[city]=...
     */
    protected $name = 'city';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: cities.ID
     */
    protected $column = 'cities.ID';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer
     */
    protected $type = 'integer';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> cities
     */
    protected $joins = [
        'table' => 'cities',
        'alias' => 'cities',
        'on'    => 'sessions.city_id = cities.ID',
    ];

    /**
     * UI input component type.
     *
     * @var string Input type: searchable
     */
    protected $inputType = 'searchable';

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
    protected $groups = ['visitors', 'views', 'individual-content', 'individual-category', 'individual-author'];

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

        $sql = "SELECT ID as value, city_name as label FROM {$table}";

        if (!empty($search)) {
            $sql .= $wpdb->prepare(" WHERE city_name LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        $sql .= " ORDER BY city_name ASC LIMIT %d";
        $sql  = $wpdb->prepare($sql, $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results ?: [];
    }
}
