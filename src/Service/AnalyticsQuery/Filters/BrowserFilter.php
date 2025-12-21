<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Browser filter - filters by browser name.
 *
 * @since 15.0.0
 */
class BrowserFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[browser]=...
     */
    protected $name = 'browser';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: device_browsers.ID
     */
    protected $column = 'device_browsers.ID';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer
     */
    protected $type = 'integer';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> device_browsers
     */
    protected $joins = [
        'table' => 'device_browsers',
        'alias' => 'device_browsers',
        'on'    => 'sessions.device_browser_id = device_browsers.ID',
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
        return esc_html__('Browser', 'wp-statistics');
    }

    /**
     * Search browser options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_device_browsers';

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
