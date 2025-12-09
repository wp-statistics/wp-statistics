<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Browser filter - filters by browser name.
 *
 * @since 15.0.0
 */
class BrowserFilter extends AbstractFilter
{
    protected $name   = 'browser';
    protected $column = 'device_browsers.name';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'device_browsers',
        'alias' => 'device_browsers',
        'on'    => 'sessions.device_browser_id = device_browsers.ID',
    ];

    protected $inputType          = 'searchable';
    protected $supportedOperators = ['is', 'is_not'];
    protected $pages              = [
        'visitors-overview',
        'visitors',
        'online-visitors',
        'top-visitors',
        'views',
        'geographic',
        'search-engines',
        'social-media',
    ];

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
