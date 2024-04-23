<?php

namespace WP_Statistics\Service\ContentAnalytics;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use InvalidArgumentException;

class ContentAnalyticsPage
{

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
            if (isset($_GET['tab']) && !in_array($_GET['tab'], array_keys($this->getTabs()))) {
                throw new InvalidArgumentException(esc_html__('Invalid tab provided.', 'wp-statistics'));
            }
        }
    }

    /**
     * Get active tab
     * 
     * @return string
     */
    public function getCurrentTab()
    {
        return isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'post';
    }

    /**
     * Get available tabs
     * 
     * @return array
     */
    public function getTabs()
    {
        $currentTab = $this->getCurrentTab();

        $tabs = [
            'post' => [
                'link'    => Menus::admin_url(Menus::get_page_slug('content-analytics'), ['tab' => 'post']),
                'title'   => esc_html__('Posts', 'wp-statistics'),
                'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                'class'   => $currentTab === 'post' ? 'current' : '',
            ],
            'page' => [
                'link'    => Menus::admin_url(Menus::get_page_slug('content-analytics'), ['tab' => 'page']),
                'title'   => esc_html__('Pages', 'wp-statistics'),
                'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                'class'   => $currentTab === 'page' ? 'current' : '',
            ]
        ];

        return apply_filters('wp_statistics_content_analytics_tabs', $tabs, $currentTab);
    }

    /**
     * Get tab data
     * 
     * @param string
     * @return array
     */
    public function getTabData($currentTab)
    {
        $data = [];

        // Todo: get data based on current tab

        $data = apply_filters('wp_statistics_content_analytics_tab_data', $data, $currentTab);
        return $data;
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
        $currentTab = $this->getCurrentTab();

        $args = [
            'title'      => esc_html__('Content Analytics', 'wp-statistics'),
            'tooltip'    => esc_html__('Page Tooltip', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('content-analytics'),
            'pagination' => Admin_Template::getCurrentPaged(),
            'custom_get' => ['tab' => $currentTab],
            'DateRang'   => Admin_Template::DateRange(),
            'tabs'       => $this->getTabs(),
            'data'       => $this->getTabData($currentTab)
        ];

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/content-analytics/$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
    }

}
