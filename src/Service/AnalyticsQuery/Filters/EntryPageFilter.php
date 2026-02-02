<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Entry page filter - filters by the first page visited in a session.
 *
 * @since 15.0.0
 */
class EntryPageFilter extends AbstractFilter
{
    protected $name = 'entry_page';
    protected $column = 'entry_page_uris.uri';
    protected $type = 'string';
    protected $inputType = 'searchable';
    protected $requirement = 'sessions';
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];
    protected $groups = ['visitors', 'views', 'referrals'];
    protected $joins = [
        [
            'table' => 'views',
            'alias' => 'entry_views',
            'on'    => 'sessions.initial_view_id = entry_views.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resource_uris',
            'alias' => 'entry_page_uris',
            'on'    => 'entry_views.resource_uri_id = entry_page_uris.ID',
            'type'  => 'LEFT',
        ],
        [
            'table' => 'resources',
            'alias' => 'entry_page_resources',
            'on'    => 'entry_page_uris.resource_id = entry_page_resources.ID',
            'type'  => 'LEFT',
        ],
    ];

    public function getLabel(): string
    {
        return esc_html__('Entry Page', 'wp-statistics');
    }

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
