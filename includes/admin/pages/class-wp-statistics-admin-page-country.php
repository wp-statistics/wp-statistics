<?php

namespace WP_STATISTICS;

class country_page
{

    public function __construct()
    {

        if (Menus::in_page('countries')) {

            // Disable Screen Option
            add_filter('screen_options_show_screen', '__return_false');

            // Set Default All Option for DatePicker
            add_filter('wp_statistics_days_ago_request', array('\WP_STATISTICS\Helper', 'set_all_option_datepicker'));

            // Is Validate Date Request
            $DateRequest = Admin_Template::isValidDateRequest();
            if (!$DateRequest['status']) {
                wp_die($DateRequest['message']);
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
        $args['title'] = __('Leading Countries by Visitor Count', 'wp-statistics');

        // Get Current Page Url
        $args['pageName'] = Menus::get_page_slug('countries');
        $args['paged']    = Admin_Template::getCurrentPaged();

        // Get Date-Range
        $args['DateRang'] = Admin_Template::DateRange();


        // From Date and To Date
        $days_list    = array_keys(TimeZone::getListDays(array('from' => TimeZone::getTimeAgo(30))));
        $args['from'] = !empty($_REQUEST[Admin_Template::$request_from_date]) ? sanitize_text_field($_REQUEST[Admin_Template::$request_from_date]) : reset($days_list);
        $args['to']   = !empty($_REQUEST[Admin_Template::$request_to_date]) ? sanitize_text_field($_REQUEST[Admin_Template::$request_to_date]) : end($days_list);

        // Get limit
        $args['limit'] = 25;

        // Set Limit
        Admin_Template::$item_per_page = $args['limit'];

        // Get offset
        $args['offset'] = Admin_Template::getCurrentOffset();

        /**
         * Filters the args used from pages for query stats
         *
         * @param array $args The args passed to query stats
         * @since 14.2.1
         *
         */
        $args = apply_filters('wp_statistics_pages_countries_args', $args);

        // Load List Country Code
        $ISOCountryCode = Country::getList();

        // Get List From DB
        $list = array();

        // Get Result
        $limitQuery = $wpdb->prepare("LIMIT %d, %d", $args['offset'], $args['limit']);
        $sqlQuery   = $wpdb->prepare("SELECT `location`, COUNT(`location`) AS `count` FROM `" . DB::table('visitor') . "` WHERE `last_counter` BETWEEN %s AND %s GROUP BY `location` ORDER BY `count` DESC", $args['from'], $args['to']);

        // Set Total
        $totalQuery    = $wpdb->get_results($sqlQuery);
        $args['total'] = count($totalQuery);

        // Set Result
        $result = $wpdb->get_results($sqlQuery . " " . $limitQuery);

        foreach ($result as $item) {
            $item->location = strtoupper($item->location);
            $list[]         = array(
                'location' => $item->location,
                'name'     => $ISOCountryCode[$item->location],
                'flag'     => Country::flag($item->location),
                'link'     => Menus::admin_url('visitors', array('location' => $item->location)),
                'number'   => $item->count
            );
        }

        $args['list'] = $list;

        // Create WordPress Pagination
        $args['pagination'] = '';
        if ($args['total'] > 0) {
            $args['pagination'] = Admin_Template::paginate_links(array(
                'total' => $args['total'],
                'echo'  => false
            ));
        }

        // Show Template
        Admin_Template::get_template(array('layout/header', 'layout/title', 'layout/date.range', 'pages/country', 'layout/postbox.toggle', 'layout/footer'), $args);
    }

}

new country_page;