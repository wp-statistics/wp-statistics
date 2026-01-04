<?php

namespace WP_Statistics\Service\Admin\ReactApp\Providers;

use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;

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
                'icon'  => 'LayoutDashboard',
                'label' => esc_html__('Overview', 'wp-statistics'),
                'slug'  => 'overview'
            ],
            'visitorInsights'   => [
                'icon'     => 'User',
                'label'    => esc_html__('Visitor Insights', 'wp-statistics'),
                'slug'     => 'visitor-insights',
                'subPages' => [
                    'visitorsOverview' => [
                        'label' => esc_html__('Visitors Overview', 'wp-statistics'),
                        'slug'  => 'visitors-overview'
                    ],
                    'visitors'         => [
                        'label' => esc_html__('Visitors', 'wp-statistics'),
                        'slug'  => 'visitors'
                    ],
                    'views'            => [
                        'label' => esc_html__('Views', 'wp-statistics'),
                        'slug'  => 'views'
                    ],
                    'onlineVisitors'   => [
                        'label' => esc_html__('Online Visitors', 'wp-statistics'),
                        'slug'  => 'online-visitors'
                    ],
                    'topVisitors'      => [
                        'label' => esc_html__('Top Visitors', 'wp-statistics'),
                        'slug'  => 'top-visitors'
                    ],
                    'loggedInUsers'    => [
                        'label' => esc_html__('Logged-in Users', 'wp-statistics'),
                        'slug'  => 'logged-in-users'
                    ],
                    'searchTerms'      => [
                        'label' => esc_html__('Search Terms', 'wp-statistics'),
                        'slug'  => 'search-terms'
                    ]
                ]
            ],
            'pageInsights'      => [
                'icon'     => 'File',
                'label'    => esc_html__('Page Insights', 'wp-statistics'),
                'slug'     => 'page-insights',
                'subPages' => [
                    'overview'      => [
                        'label' => esc_html__('Overview', 'wp-statistics'),
                        'slug'  => 'page-insights-overview'
                    ],
                    'topPages'      => [
                        'label' => esc_html__('Top Pages', 'wp-statistics'),
                        'slug'  => 'top-pages'
                    ],
                    'entryPages'    => [
                        'label' => esc_html__('Entry Pages', 'wp-statistics'),
                        'slug'  => 'entry-pages'
                    ],
                    'exitPages'     => [
                        'label' => esc_html__('Exit Pages', 'wp-statistics'),
                        'slug'  => 'exit-pages'
                    ],
                    'categoryPages' => [
                        'label' => esc_html__('Category Pages', 'wp-statistics'),
                        'slug'  => 'category-pages'
                    ],
                    'authorPages'   => [
                        'label' => esc_html__('Author Pages', 'wp-statistics'),
                        'slug'  => 'author-pages'
                    ],
                    'pages404'      => [
                        'label' => esc_html__('404 Pages', 'wp-statistics'),
                        'slug'  => '404-pages'
                    ]
                ]
            ],
            'contentAnalytics'  => [
                'icon'     => 'FileChartColumn',
                'label'    => esc_html__('Content Analytics', 'wp-statistics'),
                'slug'     => 'content-analytics',
                'subPages' => [
                    'content'    => [
                        'label' => esc_html__('Content', 'wp-statistics'),
                        'slug'  => 'content'
                    ],
                    'authors'    => [
                        'label' => esc_html__('Authors', 'wp-statistics'),
                        'slug'  => 'authors'
                    ],
                    'categories' => [
                        'label' => esc_html__('Categories', 'wp-statistics'),
                        'slug'  => 'categories'
                    ]
                ]
            ],
            'referrals'         => [
                'icon'     => 'RefreshCw',
                'label'    => esc_html__('Referrals', 'wp-statistics'),
                'slug'     => 'referrals',
                'subPages' => [
                    'referralsOverview' => [
                        'label' => esc_html__('Referrals Overview', 'wp-statistics'),
                        'slug'  => 'referrals-overview'
                    ],
                    'referredVisitors'  => [
                        'label' => esc_html__('Referred Visitors', 'wp-statistics'),
                        'slug'  => 'referred-visitors'
                    ],
                    'referrers'         => [
                        'label' => esc_html__('Referrers', 'wp-statistics'),
                        'slug'  => 'referrers'
                    ],
                    'searchEngines'     => [
                        'label' => esc_html__('Search Engines', 'wp-statistics'),
                        'slug'  => 'search-engines'
                    ],
                    'socialMedia'       => [
                        'label' => esc_html__('Social Media', 'wp-statistics'),
                        'slug'  => 'social-media'
                    ],
                    'sourceCategories'  => [
                        'label' => esc_html__('Source Categories', 'wp-statistics'),
                        'slug'  => 'source-categories'
                    ],
                    'campaigns'         => [
                        'label' => esc_html__('Campaigns', 'wp-statistics'),
                        'slug'  => 'campaigns'
                    ]
                ]
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

