<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Region filter - filters by region/state.
 *
 * @since 15.0.0
 */
class RegionFilter extends AbstractFilter
{
    protected $name   = 'region';
    protected $column = 'cities.region_name';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'cities',
        'alias' => 'cities',
        'on'    => 'sessions.city_id = cities.ID',
    ];

    protected $inputType          = 'searchable';
    protected $supportedOperators = ['is', 'is_not'];
    protected $groups             = ['visitors'];

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
