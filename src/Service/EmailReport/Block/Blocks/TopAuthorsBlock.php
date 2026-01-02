<?php

namespace WP_Statistics\Service\EmailReport\Block\Blocks;

use WP_Statistics\Service\EmailReport\Block\AbstractBlock;

/**
 * Top Authors Block
 *
 * Displays top performing authors.
 *
 * @package WP_Statistics\Service\EmailReport\Block\Blocks
 * @since 15.0.0
 */
class TopAuthorsBlock extends AbstractBlock
{
    protected string $type = 'top-authors';
    protected string $name = 'Top Authors';
    protected string $description = 'Show most viewed authors';
    protected string $icon = 'admin-users';
    protected string $category = 'data';

    /**
     * @inheritDoc
     */
    public function getDefaultSettings(): array
    {
        return [
            'limit' => 5,
            'showViews' => true,
            'showAvatar' => true,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'limit',
                'type' => 'select',
                'label' => __('Number of Authors', 'wp-statistics'),
                'options' => [
                    ['value' => 3, 'label' => '3'],
                    ['value' => 5, 'label' => '5'],
                    ['value' => 10, 'label' => '10'],
                ],
                'default' => 5,
            ],
            [
                'name' => 'showViews',
                'type' => 'toggle',
                'label' => __('Show Views Count', 'wp-statistics'),
                'default' => true,
            ],
            [
                'name' => 'showAvatar',
                'type' => 'toggle',
                'label' => __('Show Avatar', 'wp-statistics'),
                'default' => true,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getData(array $settings, string $period): array
    {
        global $wpdb;

        $settings = wp_parse_args($settings, $this->getDefaultSettings());
        $dateRange = $this->getDateRange($period);

        $pagesTable = $wpdb->prefix . 'statistics_pages';
        $postsTable = $wpdb->posts;

        // Get views by author
        $authors = $wpdb->get_results($wpdb->prepare(
            "SELECT
                posts.post_author AS author_id,
                SUM(pages.count) AS views
            FROM {$pagesTable} pages
            INNER JOIN {$postsTable} posts ON pages.id = posts.ID
            WHERE pages.date BETWEEN %s AND %s
                AND posts.post_type = 'post'
                AND posts.post_status = 'publish'
            GROUP BY posts.post_author
            ORDER BY views DESC
            LIMIT %d",
            $dateRange['start_date'],
            $dateRange['end_date'],
            $settings['limit']
        ));

        $formattedAuthors = [];
        foreach ($authors as $author) {
            $user = get_userdata($author->author_id);
            if (!$user) {
                continue;
            }

            $formattedAuthors[] = [
                'id' => $author->author_id,
                'name' => $user->display_name,
                'url' => get_author_posts_url($author->author_id),
                'avatar' => get_avatar_url($author->author_id, ['size' => 48]),
                'views' => intval($author->views),
                'viewsFormatted' => $this->formatNumber($author->views),
            ];
        }

        return [
            'authors' => $formattedAuthors,
            'hasData' => !empty($formattedAuthors),
        ];
    }

    /**
     * @inheritDoc
     */
    public function render(array $settings, array $data, array $globalSettings): string
    {
        $settings = wp_parse_args($settings, $this->getDefaultSettings());

        return $this->renderTemplate('top-authors', [
            'settings' => $settings,
            'data' => $data,
            'globalSettings' => $globalSettings,
        ]);
    }
}
