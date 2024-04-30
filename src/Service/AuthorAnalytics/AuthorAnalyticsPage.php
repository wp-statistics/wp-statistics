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

    private static $reports = [
        'authors',
        'posts'
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

            // Throw error when invalid report provided
            if (isset($_GET['report']) && !in_array($_GET['report'], self::$reports)) {
                throw new InvalidArgumentException(esc_html__('Invalid report provided.', 'wp-statistics'));
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
        $authorID   = isset($_GET['author_id']) ? intval($_GET['author_id']) : false;
        $reportType = isset($_GET['report']) ? sanitize_text_field($_GET['report']) : false;

        // Show author posts report view
        if ($reportType == 'posts') {
            return $this->postsReportView();
        } 

        // Show all authors report view
        if ($reportType == 'authors') {
            return $this->authorsReportView();
        }

        // Show single author view
        if ($authorID) {
            return $this->singleAuthorView();
        }
        
        // Show main tabs view
        return $this->tabsView();
      }

    /**
     * Display authors template
     */
    private function tabsView()
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
     * Display authors report template
     */
    private function authorsReportView()
    {
        $args = [
            'title'      => esc_html__('Authors', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('author-lists'),
            'pagination' => Admin_Template::getCurrentPaged(),
            'DateRang'   => Admin_Template::DateRange(),
            'HasDateRang'=> true,
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/authors-report', 'layout/postbox.toggle', 'layout/footer'], $args);
    }


    /**
     * Display author posts template
     */
    private function postsReportView()
    {
        $authorID = isset($_GET['author_id']) ? sanitize_text_field($_GET['author_id']) : false;

        if (!$authorID) {
            throw new InvalidArgumentException(esc_html__('Author ID must be provided.', 'wp-statistics'));
        }

        $args = [
            'title'      => esc_html__('Posts', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('author-posts'),
            'pagination' => Admin_Template::getCurrentPaged(),
            'custom_get' => ['author_id' => $authorID],
            'DateRang'   => Admin_Template::DateRange(),
            'HasDateRang'=> true,
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/author-posts', 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}
