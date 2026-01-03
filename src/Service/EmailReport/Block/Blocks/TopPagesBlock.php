<?php

namespace WP_Statistics\Service\EmailReport\Block\Blocks;

use WP_Statistics\Service\EmailReport\Block\AbstractBlock;

/**
 * Top Pages Block
 *
 * Displays top performing pages.
 *
 * @package WP_Statistics\Service\EmailReport\Block\Blocks
 * @since 15.0.0
 */
class TopPagesBlock extends AbstractBlock
{
    protected string $type = 'top-pages';
    protected string $name = 'Top Pages';
    protected string $description = 'Show most visited pages';
    protected string $icon = 'admin-page';
    protected string $category = 'data';

    /**
     * @inheritDoc
     */
    public function getDefaultSettings(): array
    {
        return [
            'limit' => 5,
            'showViews' => true,
            'showVisitors' => true,
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
                'label' => __('Number of Pages', 'wp-statistics'),
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
                'name' => 'showVisitors',
                'type' => 'toggle',
                'label' => __('Show Visitors Count', 'wp-statistics'),
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

        // Get top pages from the pages table
        $pagesTable = $wpdb->prefix . 'statistics_pages';
        $visitorsTable = $wpdb->prefix . 'statistics_visitor_relationships';

        $pages = $wpdb->get_results($wpdb->prepare(
            "SELECT
                p.uri,
                p.id AS page_id,
                SUM(p.count) AS views,
                COUNT(DISTINCT vr.visitor_id) AS visitors
            FROM {$pagesTable} p
            LEFT JOIN {$visitorsTable} vr ON p.page_id = vr.page_id
            WHERE p.date BETWEEN %s AND %s
            GROUP BY p.uri
            ORDER BY views DESC
            LIMIT %d",
            $dateRange['start_date'],
            $dateRange['end_date'],
            $settings['limit']
        ));

        $formattedPages = [];
        foreach ($pages as $page) {
            $postId = url_to_postid(home_url($page->uri));
            $title = $postId ? get_the_title($postId) : $page->uri;

            $formattedPages[] = [
                'title' => $title ?: $page->uri,
                'url' => home_url($page->uri),
                'views' => intval($page->views),
                'visitors' => intval($page->visitors),
                'viewsFormatted' => $this->formatNumber($page->views),
                'visitorsFormatted' => $this->formatNumber($page->visitors),
            ];
        }

        return [
            'pages' => $formattedPages,
            'hasData' => !empty($formattedPages),
        ];
    }

    /**
     * @inheritDoc
     */
    public function render(array $settings, array $data, array $globalSettings): string
    {
        $settings = wp_parse_args($settings, $this->getDefaultSettings());

        return $this->renderTemplate('top-pages', [
            'settings' => $settings,
            'data' => $data,
            'globalSettings' => $globalSettings,
        ]);
    }
}
