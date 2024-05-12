<?php

namespace WP_STATISTICS;
use WP_Statistics\Components\Singleton;

class exclusions_page extends Singleton
{

    public function __construct()
    {

        // Check if in Hits Page
        if (Menus::in_page('exclusions')) {

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
        global $wpdb;

        // Page title
        $args['title'] = __('Statistics on Excluded Data', 'wp-statistics');

        // Get Current Page Url
        $args['pageName']   = Menus::get_page_slug('exclusions');
        $args['pagination'] = Admin_Template::getCurrentPaged();

        // Get Date-Range
        $args['DateRang']    = Admin_Template::DateRange();
        $args['HasDateRang'] = True;

        // Get Total Exclusions
        $args['total_exclusions'] = $wpdb->get_var("SELECT SUM(count) FROM " . DB::table('exclusions'));

        if (!$args['total_exclusions']) {
            $args['total_exclusions'] = 0;
        }

        // Show Template Page
        Admin_Template::get_template(array('layout/header', 'layout/title', 'pages/exclusions', 'layout/footer'), $args);
    }

}

exclusions_page::instance();