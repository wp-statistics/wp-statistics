<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Timezone filter - filters by visitor timezone.
 *
 * @since 15.0.0
 */
class TimezoneFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[timezone]=...
     */
    protected $name = 'timezone';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: timezones.ID
     */
    protected $column = 'timezones.ID';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer
     */
    protected $type = 'integer';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> timezones
     */
    protected $joins = [
        'table' => 'timezones',
        'alias' => 'timezones',
        'on'    => 'sessions.timezone_id = timezones.ID',
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
