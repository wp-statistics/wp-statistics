<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Providers;

use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\LocalizeDataProviderInterface;

/**
 * Provider for layout configuration data.
 *
 * This provider is responsible for preparing and delivering layout
 * structure data to the React dashboard application, including sidebar
 * navigation items and other layout components.
 *
 * @since 15.0.0
 */
class LayoutDataProvider implements LocalizeDataProviderInterface
{
    /**
     * Get layout configuration data.
     *
     * Returns an array of layout configuration including sidebar menu items
     * with their configuration such as slug, icon, and localized label.
     *
     * @return array Array of layout configuration data
     */
    public function getData()
    {
        $items['sidebar'] = [
            'overview'          => [
                'icon'  => 'Gauge',
                'label' => esc_html__('General', 'wp-statistics'),
                'slug'  => 'overview'
            ],
            'visitorInsights'   => [
                'icon'  => 'User',
                'label' => esc_html__('Visitor Insights', 'wp-statistics'),
                'slug'  => 'visitor-insights'
            ],
            'pageAnalytics'     => [
                'icon'  => 'File',
                'label' => esc_html__('Page Insights', 'wp-statistics'),
                'slug'  => 'page-analytics'
            ],
            'referrals'         => [
                'icon'     => 'RefreshCw',
                'label'    => esc_html__('Referrals', 'wp-statistics'),
                'slug'     => 'referrals',
                'subPages' => [
                    'referredVisitors' => [
                        'label' => esc_html__('Referred Visitors', 'wp-statistics'),
                        'slug'  => 'referred-visitors'
                    ],
                    'referrers'        => [
                        'label' => esc_html__('Referrers', 'wp-statistics'),
                        'slug'  => 'referrers'
                    ],
                    'searchEngines'    => [
                        'label' => esc_html__('Search Engines', 'wp-statistics'),
                        'slug'  => 'search-engines'
                    ],
                    'socialMedia'      => [
                        'label' => esc_html__('Social Media', 'wp-statistics'),
                        'slug'  => 'social-media'
                    ],
                    'sourceCategories' => [
                        'label' => esc_html__('Source Categories', 'wp-statistics'),
                        'slug'  => 'source-categories'
                    ]
                ]
            ],
            'categoryAnalytics' => [
                'icon'  => 'FileChartColumn',
                'label' => esc_html__('Content Analytics', 'wp-statistics'),
                'slug'  => 'category-analytics'
            ],
            'authorAnalytics'   => [
                'icon'  => 'FileUser',
                'label' => esc_html__('Author Analytics', 'wp-statistics'),
                'slug'  => 'author-analytics'
            ],
            'categoryAnalytics' => [
                'icon'  => 'SquareChartGantt',
                'label' => esc_html__('Category Analytics', 'wp-statistics'),
                'slug'  => 'category-analytics'
            ],
            'geographic'        => [
                'icon'  => 'Earth',
                'label' => esc_html__('Geographics', 'wp-statistics'),
                'slug'  => 'geographic'
            ],
            'devices'           => [
                'icon'  => 'MonitorSmartphone',
                'label' => esc_html__('Devices', 'wp-statistics'),
                'slug'  => 'devices'
            ]
        ];

        /**
         * Filter layout data before sending to React.
         *
         * Allows other components to add, remove, or modify layout configuration.
         *
         * @param array $items Array of layout configuration data
         * @since 15.0.0
         */
        return apply_filters('wp_statistics_dashboard_layout_data', $items);
    }

    /**
     * Get the localize data key.
     *
     * @return string The key 'layout' for layout configuration data
     */
    public function getKey()
    {
        return 'layout';
    }
}

