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

            // Throw error when invalid date provided
            $DateRequest = Admin_Template::isValidDateRequest();
            if (!$DateRequest['status']) {
                throw new InvalidArgumentException(esc_html($DateRequest['message']));
            }

            // Throw error when invalid tab provided
            if (isset($_GET['tab']) && !in_array($_GET['tab'], self::$tabs)) {
                throw new InvalidArgumentException(esc_html__('Invalid tab provided.', 'wp-statistics'));
            }

            // Throw error when invalid author ID provided
            if (isset($_GET['author_id']) && !User::exists(intval($_GET['author_id']))) {
                throw new InvalidArgumentException(esc_html__('Invalid author ID provided.', 'wp-statistics'));
            }
        }
    }

    /**
     * Display HTML
     */
    public function view()
    {
        // If Author ID is set show single author template, otherwise, show authors analytics template
        isset($_GET['author_id']) ? $this->singleAuthorView() : $this->authorsView();

      }

    /**
     * Display authors template
     */
    private function authorsView()
    {
        $currentTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'performance';

        $args = [
            'title'      => esc_html__('Author Analytics', 'wp-statistics'),
            'tooltip'    => esc_html__('Page Tooltip', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('author-analytics'),
            'pagination' => Admin_Template::getCurrentPaged(),
            'custom_get' => ['tab' => $currentTab],
            'DateRang' => Admin_Template::DateRange(),
            'tabs'       => [
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
            ],
        ];

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/author-analytics/authors-$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
    }

    /**
     * Display single author template
     */
    private function singleAuthorView()
    {
        $authorID = isset($_GET['author_id']) ? sanitize_text_field($_GET['author_id']) : '';
        $author   = get_userdata($authorID);

        $args = [
            'title'      => esc_html__('Author: ' , 'wp-statistics') . $author->display_name,
            'pageName'   => Menus::get_page_slug('author-analytics'),
            'pagination' => Admin_Template::getCurrentPaged(),
            'custom_get' => ['author_id' => $authorID],
            'DateRang'   => Admin_Template::DateRange(),
            'HasDateRang' > True,
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/author-single', 'layout/postbox.toggle', 'layout/footer'], $args);
    }


    /**
     * Display authors template
     */
    private function authorsList()
    {
        $args = [
            'title'      => esc_html__('Authors', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('author-lists'),
            'pagination' => Admin_Template::getCurrentPaged(),
            'DateRang'   => Admin_Template::DateRange(),
            'HasDateRang' > True,
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/authors-list', 'layout/postbox.toggle', 'layout/footer'], $args);
    }


    /**
     * Display single author template
     */
    private function authorPosts()
    {
        $postID = isset($_GET['POSTID']) ? sanitize_text_field($_GET['POSTID']) : '';
        $args = [
            'title'      => esc_html__('Posts', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('author-posts'),
            'pagination' => Admin_Template::getCurrentPaged(),
            'custom_get' => ['POSTID' => $postID],
            'DateRang'   => Admin_Template::DateRange(),
            'HasDateRang' > True,
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/author-posts', 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}
