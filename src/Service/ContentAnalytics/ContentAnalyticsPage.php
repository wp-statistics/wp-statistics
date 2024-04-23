<?php

namespace WP_Statistics\Service\ContentAnalytics;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use InvalidArgumentException;

class ContentAnalyticsPage
{
    private static $tabs = [
        'post',
        'page',
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
        }
    }

    /**
     * Display HTML
     */
    public function view()
    {
        $this->contentView();
    }

    /**
     * Display content template
     */
    private function contentView()
    {
        $currentTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'post';

        $args = [
            'title'      => esc_html__('Content Analytics', 'wp-statistics'),
            'tooltip'    => esc_html__('Page Tooltip', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('content-analytics'),
            'pagination' => Admin_Template::getCurrentPaged(),
            'custom_get' => ['tab' => $currentTab],
            'DateRang'   => Admin_Template::DateRange(),
            'tabs'       => [
                [
                    'link'    => Menus::admin_url(Menus::get_page_slug('content-analytics'), ['tab' => 'post']),
                    'title'   => esc_html__('Posts', 'wp-statistics'),
                    'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'post' ? 'current' : '',
                ],
                [
                    'link'    => Menus::admin_url(Menus::get_page_slug('content-analytics'), ['tab' => 'page']),
                    'title'   => esc_html__('Pages', 'wp-statistics'),
                    'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'page' ? 'current' : '',
                ]
            ]
        ];

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/content-analytics/$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
    }

}
