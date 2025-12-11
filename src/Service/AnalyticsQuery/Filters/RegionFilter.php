<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Region filter - filters by region/state.
 *
 * @since 15.0.0
 */
class RegionFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[region]=...
     */
    protected $name = 'region';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: cities.region_name
     */
    protected $column = 'cities.region_name';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

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
     * @var array Operators: is, is_not
     */
    protected $supportedOperators = ['is', 'is_not'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Region', 'wp-statistics');
    }

    /**
     * Search region options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_cities';

        $sql = "SELECT DISTINCT region_name as value, region_name as label FROM {$table} WHERE region_name IS NOT NULL AND region_name != ''";

        if (!empty($search)) {
            $sql .= $wpdb->prepare(" AND region_name LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        $sql .= " ORDER BY region_name ASC LIMIT %d";
        $sql  = $wpdb->prepare($sql, $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results ?: [];
    }
}
