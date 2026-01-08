<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * OS filter - filters by operating system name.
 *
 * @since 15.0.0
 */
class OsFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[os]=...
     */
    protected $name = 'os';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: device_oss.ID
     */
    protected $column = 'device_oss.ID';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer
     */
    protected $type = 'integer';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> device_oss
     */
    protected $joins = [
        'table' => 'device_oss',
        'alias' => 'device_oss',
        'on'    => 'sessions.device_os_id = device_oss.ID',
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
    protected $groups = ['visitors', 'views', 'individual-content'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Operating System', 'wp-statistics');
    }

    /**
     * Search OS options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_device_oss';

        $sql = "SELECT ID as value, name as label FROM {$table}";

        if (!empty($search)) {
            $sql .= $wpdb->prepare(" WHERE name LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        $sql .= " ORDER BY name ASC LIMIT %d";
        $sql  = $wpdb->prepare($sql, $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results ?: [];
    }
}
