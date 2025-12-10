<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Timezone filter - filters by visitor timezone.
 *
 * @since 15.0.0
 */
class TimezoneFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[timezone]=... */
    protected $name = 'timezone';

    /** @var string SQL column: timezone name from timezones table (e.g., America/New_York, Europe/London) */
    protected $column = 'timezones.name';

    /** @var string Data type: string for timezone name matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> timezones.
     * Links session's timezone ID to the timezone details lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'timezones',
        'alias' => 'timezones',
        'on'    => 'sessions.timezone_id = timezones.ID',
    ];

    /** @var string UI component: searchable autocomplete for timezone list */
    protected $inputType = 'searchable';

    /** @var array Supported operators: exact match and exclusion */
    protected $supportedOperators = ['is', 'is_not'];

    /** @var array Available on: visitors page for geographic/temporal analysis */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Timezone', 'wp-statistics');
    }

    /**
     * Search timezone options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_timezones';

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
