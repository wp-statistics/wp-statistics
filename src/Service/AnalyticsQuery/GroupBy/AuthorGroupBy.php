<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Author group by - groups by post author.
 *
 * @since 15.0.0
 */
class AuthorGroupBy extends AbstractGroupBy
{
    protected $name         = 'author';
    protected $column       = 'resources.cached_author_id';
    protected $alias        = 'author_id';
    protected $extraColumns = [];
    protected $joins        = [
        [
            'table' => 'resource_uris',
            'alias' => 'resource_uris',
            'on'    => 'views.resource_uri_id = resource_uris.ID',
        ],
        [
            'table' => 'resources',
            'alias' => 'resources',
            'on'    => 'resource_uris.resource_id = resources.ID',
            'type'  => 'LEFT',
        ],
    ];
    protected $groupBy      = 'resources.cached_author_id';
    protected $filter       = 'resources.cached_author_id IS NOT NULL AND resources.cached_author_id > 0';
    protected $requirement  = 'views';

    /**
     * Post-process rows to add author name and avatar URL.
     *
     * Fetches author display name from WordPress users table
     * since cached_author_name is not stored in resources table.
     *
     * @param array $rows Query result rows.
     * @param \wpdb $wpdb WordPress database object.
     * @return array Processed rows with additional author data.
     */
    public function postProcess(array $rows, \wpdb $wpdb): array
    {
        // Collect all author IDs
        $authorIds = array_filter(array_unique(array_column($rows, 'author_id')));

        if (empty($authorIds)) {
            return $rows;
        }

        // Fetch author names in a single query
        $placeholders = implode(',', array_fill(0, count($authorIds), '%d'));
        $authorData = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, display_name FROM {$wpdb->users} WHERE ID IN ({$placeholders})",
                $authorIds
            ),
            OBJECT_K
        );

        // Add author name and avatar to each row
        foreach ($rows as &$row) {
            if (!empty($row['author_id'])) {
                $authorId = $row['author_id'];
                $row['author_name'] = isset($authorData[$authorId]) ? $authorData[$authorId]->display_name : '';
                $row['author_avatar'] = esc_url(get_avatar_url($authorId));
            }
        }

        return $rows;
    }
}
