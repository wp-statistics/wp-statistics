<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * City filter - filters by city name.
 *
 * @since 15.0.0
 */
class CityFilter extends AbstractFilter
{
    protected $name   = 'city';
    protected $column = 'cities.city_name';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'cities',
        'alias' => 'cities',
        'on'    => 'sessions.city_id = cities.ID',
    ];

    protected $inputType          = 'searchable';
    protected $supportedOperators = ['is', 'is_not'];
    protected $pages              = [
        'visitors-overview',
        'visitors',
        'top-visitors',
    ];

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
