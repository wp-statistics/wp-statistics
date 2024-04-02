<?php

namespace WP_Statistics\Service\AuthorAnalytics;

use WP_STATISTICS\User;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use InvalidArgumentException;

class AuthorAnalyticsPage
{
    private static $tabs = [
        'performance',
        'pages'
    ];

    public function __construct()
    {
        // Check if in Author Analytics page
        if (Menus::in_page('author-analytics')) {
            // Disable Screen Option
            add_filter('screen_options_show_screen', '__return_false');

            // Is Validate Date Request
            $DateRequest = Admin_Template::isValidDateRequest();
            if (!$DateRequest['status']) {
                wp_die($DateRequest['message']);
            }

            // Check Validate int Params
            if (isset($_GET['ID']) and (!is_numeric($_GET['ID']) || ($_GET['ID'] != 0 and User::exists((int)trim($_GET['ID'])) === false))) {
                wp_die(esc_html__("The request is invalid.", "wp-statistics"));
            }
        }
    }

    /**
     * Display HTML
     */
    public function view()
    {
        // Get current tab
        $currentTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'performance';

        // Throw error when invalid tab provided
        if (!in_array($currentTab, self::$tabs)) {
            throw new InvalidArgumentException(esc_html__('Invalid tab provided.', 'wp-statistics'));
        }

        // Page title
        $args['title']      = esc_html__('Author Analytics', 'wp-statistics');
        $args['tooltip']    = esc_html__('Page Tooltip', 'wp-statistics');

        // Get Current Page Url
        $args['pageName']   = Menus::get_page_slug('author-analytics');
        $args['pagination'] = Admin_Template::getCurrentPaged();
        $args['custom_get'] = ['tab' => $currentTab];
        $args['tabs']       = [
            [
                'link'    => Menus::admin_url(Menus::get_page_slug('author-analytics'), ['tab' => 'performance']),
                'title'   => esc_html__('Authors Performance', 'wp-statistics'),
                'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                'class'   => $currentTab === 'performance' ? 'current' : '',
            ],
            [
                'link'    => Menus::admin_url(Menus::get_page_slug('author-analytics'), ['tab' => 'pages']),
                'title'   => esc_html__('Author Pages', 'wp-statistics'),
                'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                'class'   => $currentTab === 'pages' ? 'current' : '',
            ]
        ];

        // Get Date-Range
        $args['DateRang'] = Admin_Template::DateRange();

        // Show Template Page
        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/author-analytics/$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
    }
}
