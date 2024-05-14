<?php

namespace WP_STATISTICS;
use WP_Statistics\Components\Singleton;

class authors_page extends Singleton
{

    public function __construct()
    {

        // Check if in category Page
        if (Menus::in_page('authors')) {

            // Disable Screen Option
            add_filter('screen_options_show_screen', '__return_false');

            // Is Validate Date Request
            $DateRequest = Admin_Template::isValidDateRequest();
            if (!$DateRequest['status']) {
                wp_die(esc_html($DateRequest['message']));
            }

            // Check Validate int Params
            if (isset($_GET['ID']) and (!is_numeric($_GET['ID']) || ($_GET['ID'] != 0 and User::exists((int)trim($_GET['ID'])) === false))) {
                wp_die(__("The request is invalid.", "wp-statistics")); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	
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
        $args['title'] = __('Statistics by Author', 'wp-statistics');

        // Get Current Page Url
        $args['pageName']   = Menus::get_page_slug('authors');
        $args['pagination'] = Admin_Template::getCurrentPaged();

        // Get Date-Range
        $args['DateRang']    = Admin_Template::DateRange();
        $args['HasDateRang'] = True;

        // Get List Authors
        $users = get_users((User::Access('manage') ? array('role__in' => array('author', 'administrator')) : array('role__in' => 'author')));

        // Check Number Post From a author
        if (isset($_GET['ID']) and $_GET['ID'] > 0) {
            $args['number_post_from_user'] = count_user_posts((int)trim($_GET['ID']));
        }

        // Get Top Categories By Hits
        $args['top_list'] = array();
        if (!isset($_GET['ID']) || (isset($_GET['ID']) and $_GET['ID'] == 0)) {

            // Set Type List
            $args['top_list_type'] = 'user';
            $args['top_title']     = __('Top Authors by Page Views', 'wp-statistics');

            // Push List Category
            foreach ($users as $user) {
                $args['top_list'][$user->ID] = array('ID' => $user->ID, 'name' => User::get_name($user->ID), 'link' => add_query_arg('ID', $user->ID), 'count_visit' => (int)wp_statistics_pages('total', null, $user->ID, null, null, 'author'));
            }

        } else {

            // Set Type List
            $args['top_list_type'] = 'post';
            $args['top_title']     = __('Author’s Top Posts by Views', 'wp-statistics');

            // Get Top Posts From Category
            $post_lists = Helper::get_post_list(array(
                'post_type' => 'post',
                'author'    => sanitize_text_field($_GET['ID'])
            ));
            foreach ($post_lists as $post_id => $post_title) {
                $args['top_list'][$post_id] = array('ID' => $post_id, 'name' => $post_title, 'link' => Menus::admin_url('pages', array('ID' => $post_id)), 'count_visit' => (int)wp_statistics_pages('total', null, $post_id, null, null, 'post'));
            }

        }

        // Sort By View Count
        Helper::SortByKeyValue($args['top_list'], 'count_visit');

        $author_items = apply_filters('wp_statistics_author_items', 10);

        // Get Only 5 Item
        if (count($args['top_list']) > $author_items) {
            $args['top_list'] = array_chunk($args['top_list'], $author_items);
            $args['top_list'] = $args['top_list'][0];
        }

        // Show Template Page
        Admin_Template::get_template(array('layout/header', 'layout/title', 'pages/author', 'layout/postbox.hide', 'layout/footer'), $args);
    }

}

authors_page::instance();