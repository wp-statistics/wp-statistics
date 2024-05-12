<?php

namespace WP_Statistics\Service\PrivacyAudit;

use WP_Statistics\Components\Singleton;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;

class PrivacyAuditPage extends Singleton
{
    public function __construct()
    {
        // Check if in Privacy Audit page
        if (Menus::in_page('privacy-audit')) {
            // Disable Screen Option
            add_filter('screen_options_show_screen', '__return_false');
        }
    }

    /**
     * Display HTML
     */
    public function view()
    {
        // Page title
        $args['title']   = esc_html__('Privacy Audit', 'wp-statistics');
        $args['tooltip'] = esc_html__('Check your privacy settings here to make sure WP Statistics is set up safely. This page helps you see if any settings might be collecting personal information and guides you on how to adjust them for better privacy. It\'s an easy way to keep your site\'s data use clear and safe.', 'wp-statistics');

        // Get Current Page Url
        $args['pageName']   = Menus::get_page_slug('privacy_audit');
        $args['pagination'] = Admin_Template::getCurrentPaged();

        // Show Template Page
        Admin_Template::get_template(array('layout/header', 'layout/title', 'pages/privacy-audit', 'layout/footer'), $args);
    }
}
