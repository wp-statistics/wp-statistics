<?php

namespace WP_STATISTICS;

class privacy_audit_page
{
    public function __construct()
    {

        // Check if in Privacy Audit page
        if (Menus::in_page('privacy_audit')) {
            // Disable Screen Option
            add_filter('screen_options_show_screen', '__return_false');
        }
    }

    /**
     * Display Html Page
     *
     * @throws \Exception
     */
    public static function view()
    {

        // Page title
        $args['title'] = __('Privacy Audit', 'wp-statistics');

        // Get Current Page Url
        $args['pageName']   = Menus::get_page_slug('privacy-audit');
        $args['pagination'] = Admin_Template::getCurrentPaged();

        // Show Template Page
        Admin_Template::get_template(array('layout/header', 'layout/title', 'pages/privacy-audit', 'layout/footer'), $args);
    }
}

new privacy_audit_page;