<?php

namespace WP_Statistics\Service\Admin\WordPressIntegration;

use WP_Statistics\Components\Option;
use WP_Statistics\Utils\User;
use WP_Statistics\Utils\Format;

/**
 * Statistics columns in WordPress admin list tables.
 *
 * Adds "Views" columns to content lists (posts/pages/CPTs) and user list.
 * Controlled by `disable_column` (inverted) and `enable_user_column` settings.
 *
 * @since 15.0.0
 */
class StatsColumnManager
{
    public function __construct()
    {
        if (!User::hasAccess('read')) {
            return;
        }

        // Content list columns (inverted: disable_column=true means OFF)
        if (!Option::getValue('disable_column')) {
            add_action('admin_init', [$this, 'registerContentColumns']);
            add_action('pre_get_posts', [$this, 'handleContentColumnOrderby']);
        }

        // User list column (normal: enable_user_column=true means ON)
        if (Option::getValue('enable_user_column')) {
            add_filter('manage_users_columns', [$this, 'addUserColumn']);
            add_filter('manage_users_custom_column', [$this, 'renderUserColumnValue'], 10, 3);
            add_filter('manage_users_sortable_columns', [$this, 'makeUserColumnSortable']);
        }
    }

    // -- Content columns --

    public function registerContentColumns(): void
    {
        $postTypes = get_post_types(['public' => true]);
        unset($postTypes['attachment']);

        foreach ($postTypes as $postType) {
            add_filter("manage_{$postType}_posts_columns", [$this, 'addContentColumn']);
            add_action("manage_{$postType}_posts_custom_column", [$this, 'renderContentColumnValue'], 10, 2);
            add_filter("manage_edit-{$postType}_sortable_columns", [$this, 'makeContentColumnSortable']);
        }
    }

    public function addContentColumn(array $columns): array
    {
        $columns['wp_statistics_views'] = esc_html__('Views', 'wp-statistics');
        return $columns;
    }

    public function renderContentColumnValue(string $columnName, int $postId): void
    {
        if ($columnName !== 'wp_statistics_views') {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'statistics_pages';

        $views = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(count) FROM {$table} WHERE id = %d",
                $postId
            )
        );

        echo esc_html(Format::compactNumber($views));
    }

    public function makeContentColumnSortable(array $columns): array
    {
        $columns['wp_statistics_views'] = 'wp_statistics_views';
        return $columns;
    }

    /**
     * Handle sorting by views column in post list queries.
     */
    public function handleContentColumnOrderby(\WP_Query $query): void
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        if ($query->get('orderby') !== 'wp_statistics_views') {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'statistics_pages';

        $query->set('orderby', 'meta_value_num');
        $query->set('meta_key', '');

        // Use a custom orderby clause via posts_clauses filter
        add_filter('posts_clauses', function ($clauses) use ($table, $wpdb) {
            $clauses['join']    .= " LEFT JOIN (SELECT id, SUM(count) AS total_views FROM {$table} GROUP BY id) AS wps_views ON {$wpdb->posts}.ID = wps_views.id";
            $clauses['orderby']  = 'COALESCE(wps_views.total_views, 0) ' . ($clauses['orderby'] && strpos($clauses['orderby'], 'ASC') !== false ? 'ASC' : 'DESC');

            return $clauses;
        });
    }

    // -- User columns --

    public function addUserColumn(array $columns): array
    {
        $columns['wp_statistics_user_views'] = esc_html__('Views', 'wp-statistics');
        return $columns;
    }

    public function renderUserColumnValue(string $output, string $columnName, int $userId): string
    {
        if ($columnName !== 'wp_statistics_user_views') {
            return $output;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'statistics_pages';

        $views = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(p.count)
                 FROM {$table} p
                 INNER JOIN {$wpdb->posts} posts ON p.id = posts.ID
                 WHERE posts.post_author = %d
                 AND posts.post_status = 'publish'",
                $userId
            )
        );

        return esc_html(Format::compactNumber($views));
    }

    public function makeUserColumnSortable(array $columns): array
    {
        $columns['wp_statistics_user_views'] = 'wp_statistics_user_views';
        return $columns;
    }
}
