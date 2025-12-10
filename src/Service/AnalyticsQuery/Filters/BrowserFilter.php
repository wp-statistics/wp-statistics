<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Browser filter - filters by browser name.
 *
 * @since 15.0.0
 */
class BrowserFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[browser]=... */
    protected $name = 'browser';

    /** @var string SQL column: browser name from device_browsers lookup table (e.g., Chrome, Firefox, Safari) */
    protected $column = 'device_browsers.name';

    /** @var string Data type: string for browser name matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> device_browsers.
     * Links session's browser ID to the browser name lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'device_browsers',
        'alias' => 'device_browsers',
        'on'    => 'sessions.device_browser_id = device_browsers.ID',
    ];

    /** @var string UI component: searchable autocomplete for large browser list */
    protected $inputType = 'searchable';

    /** @var array Supported operators: exact match and exclusion */
    protected $supportedOperators = ['is', 'is_not'];

    /** @var array Available on: visitors page for device analysis */
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

        $sql = "SELECT name as value, name as label FROM {$table}";

        if (!empty($search)) {
            $sql .= $wpdb->prepare(" WHERE name LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        $sql .= " ORDER BY name ASC LIMIT %d";
        $sql  = $wpdb->prepare($sql, $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results ?: [];
    }
}
