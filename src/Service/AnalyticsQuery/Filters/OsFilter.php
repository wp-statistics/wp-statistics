<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * OS filter - filters by operating system name.
 *
 * @since 15.0.0
 */
class OsFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[os]=... */
    protected $name = 'os';

    /** @var string SQL column: OS name from device_oss table (e.g., Windows, macOS, Linux, iOS, Android) */
    protected $column = 'device_oss.name';

    /** @var string Data type: string for operating system name matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> device_oss.
     * Links session's OS ID to the operating system lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'device_oss',
        'alias' => 'device_oss',
        'on'    => 'sessions.device_os_id = device_oss.ID',
    ];

    /** @var string UI component: searchable autocomplete for OS name list */
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
