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

        // Get top pages from v15 tables
        $viewsTable = $wpdb->prefix . 'statistics_views';
        $resourcesTable = $wpdb->prefix . 'statistics_resources';

        $pages = $wpdb->get_results($wpdb->prepare(
            "SELECT
                r.resource_url,
                r.post_id,
                SUM(v.views) AS views,
                SUM(v.visitors) AS visitors
            FROM {$viewsTable} v
            INNER JOIN {$resourcesTable} r ON v.resource_id = r.id
            WHERE v.date BETWEEN %s AND %s
            GROUP BY r.id
            ORDER BY views DESC
            LIMIT %d",
            $dateRange['start_date'],
            $dateRange['end_date'],
            $settings['limit']
        ));

        $formattedPages = [];
        foreach ($pages as $page) {
            $title = $page->post_id ? get_the_title($page->post_id) : $page->resource_url;

            $formattedPages[] = [
                'title' => $title ?: $page->resource_url,
                'url' => $page->resource_url,
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
