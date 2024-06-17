<?php

namespace WP_Statistics\Service\Admin\ContentAnalytics;

use InvalidArgumentException;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\Singleton;
use WP_STATISTICS\Menus;

class ContentAnalyticsPage extends Singleton
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
     * Display HTML view
     */
    public function view()
    {
        // If post ID is set add hook to register single content view, otherwise, show tabs view
        isset($_GET['ID']) ? do_action('wp_statistics_content_analytics_single_view') : $this->tabView();
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
            ],
            'product' => [
                'link'    => Menus::admin_url(Menus::get_page_slug('content-analytics'), ['tab' => 'product']),
                'title'   => esc_html__('Products', 'wp-statistics'),
                'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                'class'   => $currentTab === 'product' ? 'current' : '',
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
     * Display tab template
     */
    private function tabView()
    {
        $currentTab = $this->getCurrentTab();
        $tabs       = $this->getTabs();
        $isAddonTab = array_key_exists('addon', $tabs[$currentTab]) ? $tabs[$currentTab]['addon'] : false;

        $args = [
            'title'      => esc_html__('Content Analytics', 'wp-statistics'),
            'tooltip'    => esc_html__('Content Analytics Tooltip', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('content-analytics'),
            'pagination' => Admin_Template::getCurrentPaged(),
            'custom_get' => ['tab' => $currentTab],
            'DateRang'   => Admin_Template::DateRange(),
            'hasDateRang' => true,
            'tabs'       => $tabs,
            'data'       => $this->getTabData($currentTab)
        ];

        // Get template If current tab is part of the core plugin, otherwise, add hook to register custom tab views
        if (!$isAddonTab) {
            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/content-analytics/$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
        } else {
            do_action('wp_statistics_content_analytics_tab_view', $currentTab, $args);
        }

    }

}
