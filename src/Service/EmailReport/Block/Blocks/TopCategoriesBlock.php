<?php

namespace WP_Statistics\Service\EmailReport\Block\Blocks;

use WP_Statistics\Service\EmailReport\Block\AbstractBlock;

/**
 * Top Categories Block
 *
 * Displays top performing categories.
 *
 * @package WP_Statistics\Service\EmailReport\Block\Blocks
 * @since 15.0.0
 */
class TopCategoriesBlock extends AbstractBlock
{
    protected string $type = 'top-categories';
    protected string $name = 'Top Categories';
    protected string $description = 'Show most viewed categories';
    protected string $icon = 'category';
    protected string $category = 'data';

    /**
     * @inheritDoc
     */
    public function getDefaultSettings(): array
    {
        return [
            'limit' => 5,
            'showViews' => true,
            'taxonomy' => 'category',
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
                'label' => __('Number of Categories', 'wp-statistics'),
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
        $termRelTable = $wpdb->term_relationships;
        $termTaxTable = $wpdb->term_taxonomy;
        $termsTable = $wpdb->terms;

        // Get views by category
        $categories = $wpdb->get_results($wpdb->prepare(
            "SELECT
                t.term_id,
                t.name,
                t.slug,
                SUM(pages.count) AS views
            FROM {$pagesTable} pages
            INNER JOIN {$postsTable} posts ON pages.id = posts.ID
            INNER JOIN {$termRelTable} tr ON posts.ID = tr.object_id
            INNER JOIN {$termTaxTable} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$termsTable} t ON tt.term_id = t.term_id
            WHERE pages.date BETWEEN %s AND %s
                AND posts.post_type = 'post'
                AND posts.post_status = 'publish'
                AND tt.taxonomy = %s
            GROUP BY t.term_id
            ORDER BY views DESC
            LIMIT %d",
            $dateRange['start_date'],
            $dateRange['end_date'],
            $settings['taxonomy'],
            $settings['limit']
        ));

        $formattedCategories = [];
        foreach ($categories as $cat) {
            $formattedCategories[] = [
                'id' => $cat->term_id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'url' => get_term_link(intval($cat->term_id), $settings['taxonomy']),
                'views' => intval($cat->views),
                'viewsFormatted' => $this->formatNumber($cat->views),
            ];
        }

        return [
            'categories' => $formattedCategories,
            'hasData' => !empty($formattedCategories),
        ];
    }

    /**
     * @inheritDoc
     */
    public function render(array $settings, array $data, array $globalSettings): string
    {
        $settings = wp_parse_args($settings, $this->getDefaultSettings());

        return $this->renderTemplate('top-categories', [
            'settings' => $settings,
            'data' => $data,
            'globalSettings' => $globalSettings,
        ]);
    }
}
