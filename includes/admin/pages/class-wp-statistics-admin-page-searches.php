<?php

namespace WP_STATISTICS;
use WP_Statistics\Components\Singleton;

class searches_page extends Singleton
{

    public function __construct()
    {

        // Check if in Hits Page
        if (Menus::in_page('searches')) {

            // Disable Screen Option
            add_filter('screen_options_show_screen', '__return_false');

            // Is Validate Date Request
            $DateRequest = Admin_Template::isValidDateRequest();
            if (!$DateRequest['status']) {
                wp_die(esc_html($DateRequest['message']));
            }
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
        $args['title'] = __('Detailed Statistics on Search Engine Referrals', 'wp-statistics');

        // Get Current Page Url
        $args['pageName']   = Menus::get_page_slug('searches');
        $args['pagination'] = Admin_Template::getCurrentPaged();

        // Get Date-Range
        $args['DateRang']    = Admin_Template::DateRange();
        $args['HasDateRang'] = True;

        // Show Template Page
        Admin_Template::get_template(array('layout/header', 'layout/title', 'pages/search', 'layout/footer'), $args);
    }

}

searches_page::instance();