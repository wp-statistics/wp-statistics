<?php

namespace WP_Statistics\Service\PrivacyAudit;

class PrivacyAuditManager
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'addMenuItems'], 11);
    }

    public function addMenuItems()
    {
        add_submenu_page(
            'wps_overview_page', 
            esc_html__('Privacy Audit', 'wp-statistics'), 
            esc_html__('Privacy Audit', 'wp-statistics'), 
            'manage_options', 
            'wps_privacy_audit_page', 
            [$this, 'renderPage'], 
            13
        );
    }


    /**
     * Render Link Tracker admin page
     */
    public function renderPage()
    {
        $page = new PrivacyAuditPage();
        $page->view();
    }
}