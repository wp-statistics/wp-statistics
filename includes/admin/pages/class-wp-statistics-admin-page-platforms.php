<?php

namespace WP_STATISTICS;
use WP_Statistics\Components\Singleton;

class platform_page extends Singleton
{

    public function __construct()
    {

        // Check if in Hits Page
        if (Menus::in_page('platform')) {

            // Disable Screen Option
            add_filter('screen_options_show_screen', '__return_false');

            // Set Default All Option for DatePicker
            add_filter('wp_statistics_days_ago_request', array('\WP_STATISTICS\Helper', 'set_all_option_datepicker'));

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
        $args['title'] = __('User Operating System Usage Statistics', 'wp-statistics');

        // Get Current Page Url
        $args['pageName']   = Menus::get_page_slug('platform');
        $args['pagination'] = Admin_Template::getCurrentPaged();

        // Get Date-Range
        $args['DateRang']    = Admin_Template::DateRange();
        $args['HasDateRang'] = True;


        // Show Template Page
        Admin_Template::get_template(array('layout/header', 'layout/title', 'pages/platforms', 'layout/postbox.toggle', 'layout/footer'), $args);
    }

}

platform_page::instance();