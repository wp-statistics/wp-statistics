<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Page filter - filters by page URI.
 *
 * @since 15.0.0
 */
class PageFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[page]=...
     */
    protected $name = 'page';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: resource_uris.uri
     */
    protected $column = 'resource_uris.uri';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * Required base table to enable this filter.
     *
     * @var string|null Table name: views
     */
    protected $requirement = 'views';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: views -> resource_uris
     */
    protected $joins = [
        'table' => 'resource_uris',
        'alias' => 'resource_uris',
        'on'    => 'views.resource_uri_id = resource_uris.ID',
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
     * @var array Groups: views
     */
    protected $groups = ['views'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Page', 'wp-statistics');
    }

    /**
     * Search page options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' (URI) and 'label' (Title - /uri/).
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $urisTable      = $wpdb->prefix . 'statistics_resource_uris';
        $resourcesTable = $wpdb->prefix . 'statistics_resources';

        $sql = "SELECT u.uri as value, r.cached_title
                FROM {$urisTable} u
                LEFT JOIN {$resourcesTable} r ON u.resource_id = r.ID";

        if (!empty($search)) {
            $searchLike = '%' . $wpdb->esc_like($search) . '%';
            $sql       .= $wpdb->prepare(
                " WHERE u.uri LIKE %s OR r.cached_title LIKE %s",
                $searchLike,
                $searchLike
            );
        }

        $sql .= " ORDER BY u.uri ASC LIMIT %d";
        $sql  = $wpdb->prepare($sql, $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        if (empty($results)) {
            return [];
        }

        $options = [];
        foreach ($results as $row) {
            $label = !empty($row['cached_title'])
                ? sprintf('%s - %s', $row['cached_title'], $row['value'])
                : $row['value'];

            $options[] = [
                'value' => $row['value'],
                'label' => $label,
            ];
        }

        return $options;
    }
}
