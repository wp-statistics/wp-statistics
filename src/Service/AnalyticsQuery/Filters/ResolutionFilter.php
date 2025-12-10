<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Resolution filter - filters by screen resolution.
 *
 * @since 15.0.0
 */
class ResolutionFilter extends AbstractFilter
{
    protected $name   = 'resolution';
    protected $column = 'CONCAT(resolutions.width, \'x\', resolutions.height)';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'resolutions',
        'alias' => 'resolutions',
        'on'    => 'sessions.resolution_id = resolutions.ID',
    ];

    protected $inputType          = 'searchable';
    protected $supportedOperators = ['is', 'is_not'];
    protected $groups             = ['visitors'];

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
