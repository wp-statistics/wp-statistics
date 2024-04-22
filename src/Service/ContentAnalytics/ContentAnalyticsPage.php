<?php

namespace WP_Statistics\Service\ContentAnalytics;

use WP_STATISTICS\User;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use InvalidArgumentException;

class ContentAnalyticsPage
{
    private static $tabs = [
        'posts',
        'pages',
        'products',
    ];

    public function __construct()
    {

        // Check if in Content Analytics page
        if (Menus::in_page('content-analytics')) {
            // Disable Screen Option
            add_filter('screen_options_show_screen', '__return_false');

            // Throw error when invalid date provided
            $DateRequest = Admin_Template::isValidDateRequest();
            if (!$DateRequest['status']) {
                throw new InvalidArgumentException(esc_html($DateRequest['message']));
            }

            // Throw error when invalid tab provided
            if (isset($_GET['tab']) && !in_array($_GET['tab'], self::$tabs)) {
                throw new InvalidArgumentException(esc_html__('Invalid tab provided.', 'wp-statistics'));
            }

            // Throw error when invalid post ID provided
            if (isset($_GET['ID']) && is_null(get_post(intval($_GET['ID'])))) {
                throw new InvalidArgumentException(esc_html__('Invalid post ID provided.', 'wp-statistics'));
            }
        }
    }

    /**
     * Display HTML
     */
    public function view()
    {
        // If post ID is set show single content template, otherwise, show content analytics template
        isset($_GET['ID']) ? $this->singleView() : $this->contentView();
    }

    /**
     * Display content template
     */
    private function contentView()
    {
        $currentTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'posts';

        $args = [
            'title'      => esc_html__('Content Analytics', 'wp-statistics'),
            'tooltip'    => esc_html__('Page Tooltip', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('content-analytics'),
            'pagination' => Admin_Template::getCurrentPaged(),
            'custom_get' => ['tab' => $currentTab],
            'DateRang' => Admin_Template::DateRange(),
            'tabs'       => [
                [
                    'link'    => Menus::admin_url(Menus::get_page_slug('content-analytics'), ['tab' => 'posts']),
                    'title'   => esc_html__('Posts', 'wp-statistics'),
                    'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'posts' ? 'current' : '',
                ],
                [
                    'link'    => Menus::admin_url(Menus::get_page_slug('content-analytics'), ['tab' => 'pages']),
                    'title'   => esc_html__('Pages', 'wp-statistics'),
                    'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'pages' ? 'current' : '',
                ],
                [
                    'link'    => Menus::admin_url(Menus::get_page_slug('content-analytics'), ['tab' => 'products']),
                    'title'   => esc_html__('Products', 'wp-statistics'),
                    'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'products' ? 'current' : '',
                ]
            ],
        ];

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/content-analytics/content-$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
    }

    /**
     * Display single content template
     */
    private function singleView()
    {
        $postID = isset($_GET['ID']) ? sanitize_text_field($_GET['ID']) : '';

        $args = [
            'title'      => esc_html__('Single Content Analytics', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('content-analytics'),
            'pagination' => Admin_Template::getCurrentPaged(),
            'custom_get' => ['ID' => $postID],
            'DateRang'   => Admin_Template::DateRange()
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'layout/date.range', 'pages/content-analytics/content-single', 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}
