<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Resolution filter - filters by screen resolution.
 *
 * @since 15.0.0
 */
class ResolutionFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[resolution]=... */
    protected $name = 'resolution';

    /** @var string SQL column: computed resolution string (e.g., 1920x1080, 1366x768) via CONCAT */
    protected $column = 'CONCAT(resolutions.width, \'x\', resolutions.height)';

    /** @var string Data type: string for resolution matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> resolutions.
     * Links session's resolution ID to the screen resolution lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'resolutions',
        'alias' => 'resolutions',
        'on'    => 'sessions.resolution_id = resolutions.ID',
    ];

    /** @var string UI component: searchable autocomplete for resolution list */
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
        return esc_html__('Screen Resolution', 'wp-statistics');
    }

    /**
     * Search resolution options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_resolutions';

        $sql = "SELECT CONCAT(width, 'x', height) as value, CONCAT(width, 'x', height) as label FROM {$table}";

        if (!empty($search)) {
            $sql .= $wpdb->prepare(" WHERE CONCAT(width, 'x', height) LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        $sql .= " ORDER BY width DESC, height DESC LIMIT %d";
        $sql  = $wpdb->prepare($sql, $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results ?: [];
    }
}
