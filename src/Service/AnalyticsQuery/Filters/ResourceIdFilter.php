<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Resource ID filter - filters by WordPress post/resource ID.
 *
 * This filter now filters on resources.resource_id (WordPress post ID) instead of
 * views.resource_id (internal PK). This ensures correct filtering when combined
 * with post_type filter to uniquely identify content.
 *
 * @since 15.0.0
 */
class ResourceIdFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[resource_id]=...
     */
    protected $name = 'resource_id';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: resources.resource_id (WordPress post ID)
     */
    protected $column = 'resources.resource_id';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer (WordPress post ID)
     */
    protected $type = 'integer';

    /**
     * UI input component type.
     *
     * @var string Input type: searchable
     */
    protected $inputType = 'searchable';

    /**
     * Required base table to enable this filter.
     *
     * @var string|null Table name: views
     */
    protected $requirement = 'views';

    /**
     * Required JOINs to access the resources table.
     *
     * The resources JOIN includes is_deleted = 0 to exclude deleted content.
     *
     * @var array JOIN chain: views -> resource_uris -> resources
     */
    protected $joins = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'views.resource_uri_id = resource_uris.ID',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID AND resources.is_deleted = 0',
        ],
    ];

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
        return esc_html__('Resource', 'wp-statistics');
    }

    /**
     * Search resource options via AJAX.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' (ID) and 'label' (Title (Type)).
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'statistics_resources';

        $sql = "SELECT ID as value, cached_title, resource_type FROM {$table} WHERE is_deleted = 0";

        if (!empty($search)) {
            $searchLike = '%' . $wpdb->esc_like($search) . '%';
            $sql       .= $wpdb->prepare(
                " AND cached_title LIKE %s",
                $searchLike
            );
        }

        $sql .= " ORDER BY cached_title ASC LIMIT %d";
        $sql  = $wpdb->prepare($sql, $limit);

        $results = $wpdb->get_results($sql, ARRAY_A);

        if (empty($results)) {
            return [];
        }

        $options = [];
        foreach ($results as $row) {
            $typeLabel = $this->getResourceTypeLabel($row['resource_type']);

            $options[] = [
                'value' => $row['value'],
                'label' => sprintf('%s (%s)', $row['cached_title'], $typeLabel),
            ];
        }

        return $options;
    }

    /**
     * Get human-readable type label for resource type.
     *
     * @param string $resourceType Resource type.
     * @return string Type label.
     */
    private function getResourceTypeLabel(string $resourceType): string
    {
        $postTypeObject = get_post_type_object($resourceType);
        if ($postTypeObject) {
            return $postTypeObject->labels->singular_name;
        }

        $taxonomyObject = get_taxonomy($resourceType);
        if ($taxonomyObject) {
            return $taxonomyObject->labels->singular_name;
        }

        $typeLabels = [
            'home'              => esc_html__('Home', 'wp-statistics'),
            '404'               => esc_html__('404', 'wp-statistics'),
            'search'            => esc_html__('Search', 'wp-statistics'),
            'feed'              => esc_html__('Feed', 'wp-statistics'),
            'loginpage'         => esc_html__('Login', 'wp-statistics'),
            'archive'           => esc_html__('Archive', 'wp-statistics'),
            'author_archive'    => esc_html__('Author', 'wp-statistics'),
            'date_archive'      => esc_html__('Date', 'wp-statistics'),
            'post_type_archive' => esc_html__('Archive', 'wp-statistics'),
        ];

        return $typeLabels[$resourceType] ?? $resourceType;
    }
}
