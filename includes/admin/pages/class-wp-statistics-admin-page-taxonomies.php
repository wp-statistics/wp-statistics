<?php

namespace WP_STATISTICS;

class taxonomies_page
{
    private static $taxonomies = [];
    private static $taxonomy = 'category';

    public function __construct()
    {

        // Check if in taxonomies Page
        if (Menus::in_page('taxonomies')) {

            // Disable Screen Option
            add_filter('screen_options_show_screen', '__return_false');

            // Is Validate Date Request
            $DateRequest = Admin_Template::isValidDateRequest();
            if (!$DateRequest['status']) {
                wp_die($DateRequest['message']);
            }

            // Get all taxonomies
            add_action('init', [__CLASS__, 'get_list_taxonomies']);

            // Check validate taxonomy
            if (!empty($_GET['taxonomy']) && in_array($_GET['taxonomy'], ['category', 'post_tag'])) {
                self::$taxonomy = $_GET['taxonomy'];
            } else {
                wp_redirect(add_query_arg([
                    'taxonomy' => self::$taxonomy
                ], admin_url('admin.php?page=' . Menus::get_page_slug('taxonomies'))));
                exit;
            }

            // Check Validate int Params
            if (isset($_GET['ID']) and (!is_numeric($_GET['ID']) || ($_GET['ID'] != 0 and term_exists((int)trim($_GET['ID']), self::$taxonomy) == null))) {
                wp_die(__("Request is not valid.", "wp-statistics"));
            }
        }
    }

    public static function get_list_taxonomies()
    {
        self::$taxonomies = Helper::get_list_taxonomy();
    }

    /**
     * Display Html Page
     *
     * @throws \Exception
     */
    public static function view()
    {
        // Page title
        $taxonomyTitle = array_key_exists(self::$taxonomy, self::$taxonomies) ? self::$taxonomies[self::$taxonomy] : '';
        $args['title'] = __($taxonomyTitle . ' Statistics', 'wp-statistics');

        // Taxonomy
        $args['taxonomies']    = self::$taxonomies;
        $args['taxonomy']      = self::$taxonomy;
        $args['taxonomyTitle'] = $taxonomyTitle;

        // Get Current Page Url
        $args['pageName']   = Menus::get_page_slug('taxonomies');
        $args['pagination'] = Admin_Template::getCurrentPaged();

        // Get Date-Range
        $args['DateRang'] = Admin_Template::DateRange();

        // Get List Category
        $terms = get_terms(self::$taxonomy, array(
            'hide_empty' => true,
        ));

        // Check Number Post From Category
        if (isset($_GET['ID']) and $_GET['ID'] > 0) {
            $this_item                       = get_term_by('id', (int)trim($_GET['ID']), self::$taxonomy);
            $args['number_post_in_taxonomy'] = $this_item->count;
        }

        // Get Top Categories By Hits
        $args['top_list'] = array();
        if (!isset($_GET['ID']) || (isset($_GET['ID']) and $_GET['ID'] == 0)) {

            // Set Type List
            $args['top_list_type'] = self::$taxonomy;
            $args['top_title']     = __('Top ' . $taxonomyTitle . ' Sorted by Hits', 'wp-statistics');

            // Push List Category
            foreach ($terms as $term) {
                $args['top_list'][$term->term_id] = array('ID' => $term->term_id, 'name' => $term->name, 'link' => add_query_arg('ID', $term->term_id), 'count_visit' => (int)wp_statistics_pages('total', null, $term->term_id, null, null, self::$taxonomy));
            }

        } else {

            // Set Type List
            $args['top_list_type'] = 'post';
            $args['top_title']     = __('Top posts Sorted by Hits in this taxonomy', 'wp-statistics');

            // Get Top Posts From Category
            $post_lists = Helper::get_post_list(array(
                'post_type' => 'post',
                'tax_query' => [
                    [
                        'taxonomy' => self::$taxonomy,
                        'field'    => 'term_id',
                        'terms'    => sanitize_text_field($_GET['ID'])
                    ]
                ],
            ));
            foreach ($post_lists as $post_id => $post_title) {
                $args['top_list'][$post_id] = array('ID' => $post_id, 'name' => $post_title, 'link' => Menus::admin_url('pages', array('ID' => $post_id)), 'count_visit' => (int)wp_statistics_pages('total', null, $post_id, null, null, 'post'));
            }

        }

        // Sort By Visit Count
        Helper::SortByKeyValue($args['top_list'], 'count_visit');

        // Get Only 5 Item
        if (count($args['top_list']) > 5) {
            $args['top_list'] = array_chunk($args['top_list'], 5);
            $args['top_list'] = $args['top_list'][0];
        }

        // Show Template Page
        Admin_Template::get_template(array('layout/header', 'layout/title', 'layout/date.range', 'pages/taxonomies', 'layout/postbox.hide', 'layout/footer'), $args);
    }

}

new taxonomies_page;