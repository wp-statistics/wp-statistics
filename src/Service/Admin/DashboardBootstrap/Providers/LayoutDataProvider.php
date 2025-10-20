<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Providers;

use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\LocalizeDataProviderInterface;

/**
 * Provider for sidebar navigation data.
 *
 * This provider is responsible for preparing and delivering sidebar
 * navigation items to the React dashboard application.
 *
 * @since 15.0.0
 */
class LayoutDataProvider implements LocalizeDataProviderInterface
{
    /**
     * Get sidebar navigation data.
     *
     * Returns an array of sidebar menu items with their configuration
     * including slug, icon, and localized label.
     *
     * @return array Array of sidebar menu items
     */
    public function getData()
    {
        $items['sidebar'] = [
            'overview'           => [
                'icon'  => 'overview',
                'label' => esc_html__('General', 'wp-statistics'),
            ],
            'visitor-insights'   => [
                'icon'  => 'visitor-insights',
                'label' => esc_html__('Visitor Insights', 'wp-statistics')
            ],
            'page-analytics'     => [
                'icon'  => 'page-analytics',
                'label' => esc_html__('Page Insights', 'wp-statistics')
            ],
            'referrals'          => [
                'icon'      => 'referrals',
                'label'     => esc_html__('Referrals', 'wp-statistics'),
                'sub-pages' => [
                    'referred-visitors' => [
                        'label' => esc_html__('Referred Visitors', 'wp-statistics')
                    ],
                    'referrers'         => [
                        'label' => esc_html__('Referrers', 'wp-statistics')
                    ],
                    'search-engines'    => [
                        'label' => esc_html__('Search Engines', 'wp-statistics')
                    ],
                    'social-media'      => [
                        'label' => esc_html__('Social Media', 'wp-statistics')
                    ],
                    'source-categories' => [
                        'label' => esc_html__('Source Categories', 'wp-statistics')
                    ]
                ]
            ],
            'category-analytics' => [
                'icon'  => 'category-analytics',
                'label' => esc_html__('Content Analytics', 'wp-statistics')
            ],
            'author-analytics'   => [
                'icon'  => 'author-analytics',
                'label' => esc_html__('Author Analytics', 'wp-statistics')
            ],
            'category-analytics' => [
                'icon'  => 'category-analytics',
                'label' => esc_html__('Category Analytics', 'wp-statistics')
            ],
            'geographic'         => [
                'icon'  => 'geographic',
                'label' => esc_html__('Geographics', 'wp-statistics')
            ],
            'devices'            => [
                'icon'  => 'devices',
                'label' => esc_html__('Devices', 'wp-statistics')
            ]
        ];

        /**
         * Filter sidebar items before sending to React.
         *
         * Allows other components to add, remove, or modify sidebar items.
         *
         * @param array $items Array of sidebar menu items
         * @since 15.0.0
         */
        return apply_filters('wp_statistics_dashboard_sidebar_items', $items);
    }

    /**
     * Get the localize data key.
     *
     * @return string The key 'sidebar' for sidebar data
     */
    public function getKey()
    {
        return 'layout';
    }
}

