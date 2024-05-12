<?php

namespace WP_STATISTICS;
use WP_Statistics\Components\Singleton;

class online_page extends Singleton
{

    public function __construct()
    {

        if (Menus::in_page('online')) {
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
        $args['title'] = __('Online Users', 'wp-statistics');

        //Get Total User Online
        $args['total_user_online'] = UserOnline::get(array('fields' => 'count'));

        // Get List OF User Online
        if ($args['total_user_online'] > 0) {
            $args['user_online_list'] = UserOnline::get(array('offset' => Admin_Template::getCurrentOffset(), 'per_page' => Admin_Template::$item_per_page));
        } else {
            $args['user_online_list'] = __('No Users Currently Online on the Site.', 'wp-statistics');
        }

        // Create WordPress Pagination
        $args['pagination'] = '';
        if ($args['total_user_online'] > 0) {
            $args['pagination'] = Admin_Template::paginate_links(array(
                'total' => $args['total_user_online'],
                'echo'  => false
            ));
        }

        Admin_Template::get_template(array('layout/header', 'layout/title', 'pages/online', 'layout/footer'), $args);
    }

}

online_page::instance();