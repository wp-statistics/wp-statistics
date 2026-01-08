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
     * Columns added by postProcess (not in SQL query).
     *
     * @var array
     */
    protected $postProcessedColumns = [
        'author_name',
        'author_avatar',
        'author_email',
        'author_role',
        'author_registered',
        'author_posts_url',
        'author_profile_url',
    ];

    /**
     * Post-process rows to add author metadata.
     *
     * Fetches author display name, email, role, and other metadata
     * from WordPress users table since these aren't stored in resources table.
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
            // Still need to add default values for empty results
            foreach ($rows as &$row) {
                $row['author_name']        = null;
                $row['author_avatar']      = null;
                $row['author_email']       = null;
                $row['author_role']        = null;
                $row['author_registered']  = null;
                $row['author_posts_url']   = null;
                $row['author_profile_url'] = null;
            }
            return $rows;
        }

        // Fetch author data in a single query
        $placeholders = implode(',', array_fill(0, count($authorIds), '%d'));
        $authorData = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, display_name, user_email, user_registered FROM {$wpdb->users} WHERE ID IN ({$placeholders})",
                $authorIds
            ),
            \OBJECT_K
        );

        // Get user roles via user meta (wp_capabilities)
        $capabilitiesKey = $wpdb->prefix . 'capabilities';
        $userRoles = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s AND user_id IN ({$placeholders})",
                array_merge([$capabilitiesKey], $authorIds)
            ),
            \OBJECT_K
        );

        // Add author metadata to each row
        foreach ($rows as &$row) {
            if (!empty($row['author_id'])) {
                $authorId = $row['author_id'];

                if (isset($authorData[$authorId])) {
                    $user = $authorData[$authorId];
                    $row['author_name']       = $user->display_name;
                    $row['author_email']      = $user->user_email;
                    $row['author_registered'] = $user->user_registered;
                } else {
                    $row['author_name']       = null;
                    $row['author_email']      = null;
                    $row['author_registered'] = null;
                }

                // Get role from capabilities
                $role = null;
                if (isset($userRoles[$authorId])) {
                    $capabilities = \maybe_unserialize($userRoles[$authorId]->meta_value);
                    if (is_array($capabilities)) {
                        $role = key($capabilities);
                    }
                }
                $row['author_role'] = $role;

                // Generate URLs
                $row['author_avatar']      = \esc_url(\get_avatar_url($authorId));
                $row['author_posts_url']   = \esc_url(\admin_url('edit.php?author=' . $authorId));
                $row['author_profile_url'] = \esc_url(\admin_url('user-edit.php?user_id=' . $authorId));
            } else {
                $row['author_name']        = null;
                $row['author_avatar']      = null;
                $row['author_email']       = null;
                $row['author_role']        = null;
                $row['author_registered']  = null;
                $row['author_posts_url']   = null;
                $row['author_profile_url'] = null;
            }
        }

        return $rows;
    }
}
