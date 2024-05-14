<?php

namespace WP_STATISTICS;
use WP_Statistics\Components\Singleton;

class pages_page extends Singleton
{

    const ITEM_PER_PAGE = 20;

    const SINGLE_PAGE_COMPONENTS = [
        'summary',
        'browsers',
        'platforms',
        'useronline',
        'countries',
        'referring',
        'visitors',
        'top_visitors',
        'visitors_map',
    ];

    private static $postTypes = [];
    private static $defaultPostTypes = [];
    private static $postType = 'post';

    public function __construct()
    {
        add_action('init', [$this, 'init']);
    }

    public function init()
    {
        global $wpdb;

        if (Menus::in_page('pages')) {

            // Disable Screen Option
            add_filter('screen_options_show_screen', '__return_false');

            // Check Exist Statistics For Custom Page
            if (self::is_custom_page()) {
                /**
                 * Prepares the queries
                 * @since 13.0.8
                 */
                $pageTablePage = DB::table('pages');
                $preparedSql   = $wpdb->prepare(
                    "SELECT COUNT(*) FROM `" . $pageTablePage . "` WHERE `id` = %s AND `type` = %s",
                    sanitize_text_field($_GET['ID']),
                    sanitize_text_field($_GET['type'])
                );
                $page_count    = $wpdb->get_var($preparedSql); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared	

                if ($page_count < 1) {
                    wp_die(__('Request Invalid or Unsupported.', 'wp-statistics')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	
                }
            }

            self::$postTypes        = Helper::get_list_post_type();
            self::$defaultPostTypes = apply_filters('wp_statistics_default_post_types', ['post', 'page']);

            $postTypes = [];
            foreach (self::$postTypes as $key => $postType) {
                if (!in_array($postType, ['post', 'page', 'product'])) {
                    $postType = 'post_type_' . $postType;
                }
                $postTypes[] = $postType;
            }

            // Check validate post type
            if (!empty($_GET['type']) && in_array($_GET['type'], $postTypes)) {
                self::$postType = sanitize_text_field($_GET['type']);
            }

            // Is Validate Date Request
            $DateRequest = Admin_Template::isValidDateRequest();
            if (!$DateRequest['status']) {
                wp_die(esc_html($DateRequest['message']));
            }
        }
    }

    public static function is_custom_page()
    {
        return (isset($_GET['ID']) and isset($_GET['type']));
    }

    /**
     * Display Html Page
     *
     * @throws \Exception
     */
    public static function view()
    {

        // Check Show Custom Page
        if (self::is_custom_page()) {
            self::custom_page_statistics();
        } else {
            $postTypeSlug = self::$postType;
            if (strpos($postTypeSlug, 'post_type_') !== false) {
                $postTypeSlug = str_replace('post_type_', '', $postTypeSlug);
            }

            $object = get_post_type_object($postTypeSlug);

            if (isset($object->labels->name) && $object->labels->name) {
                $objectTitle = $object->labels->name;
            } else {
                $objectTitle = ucfirst($postTypeSlug);
            }

            // Page title
            $pageTitle     = __('Top', 'wp-statistics');
            $args['title'] = sprintf(' %s ', $objectTitle);

            // Top Trending Title
            $topTitle                   = __('Top 5 Trending', 'wp-statistics');
            $args['top_trending_title'] = sprintf('%1$s %2$s', $topTitle, $objectTitle);

            // Get Current Page Url
            $args['pageName'] = Menus::get_page_slug('pages');

            // Get Date-Range
            $args['DateRang'] = Admin_Template::DateRange();

            // Custom Get List
            $args['custom_get'] = [
                'type' => self::$postType,
            ];

            // Get List
            $args['lists'] = \WP_STATISTICS\Pages::getTop(array(
                'per_page' => self::ITEM_PER_PAGE,
                'paged'    => Admin_Template::getCurrentPaged(),
                'from'     => $args['DateRang']['from'],
                'to'       => $args['DateRang']['to'],
                'type'     => self::$postType,
            ));

            // Tabs
            $args['tabs'] = [];
            foreach (self::$postTypes as $slug) {
                $postTypeSlug = in_array($slug, ['post', 'page', 'product']) ? $slug : 'post_type_' . $slug;
                $class        = ($postTypeSlug == self::$postType ? 'current' : '');
                $link         = Menus::admin_url('wps_pages_page', ['type' => $postTypeSlug]);

                if (!in_array($postTypeSlug, self::$defaultPostTypes)) {
                    $class .= ' wps-locked';
                    $link  = sprintf('%s/product/wp-statistics-data-plus?utm_source=wp-statistics&utm_medium=link&utm_campaign=dp-cpt', WP_STATISTICS_SITE_URL);
                }

                $object = get_post_type_object($slug);

                if (isset($object->labels->singular_name) && $object->labels->singular_name) {
                    $title = $object->labels->singular_name;
                } else {
                    $title = ucfirst($slug);
                }

                $args['tabs'][] = [
                    'link'  => $link,
                    'title' => $title,
                    'class' => $class,
                ];
            }

            // Total Number
            $args['total'] = Pages::TotalCount('uri', array(
                'from' => $args['DateRang']['from'],
                'to'   => $args['DateRang']['to'],
                'type' => self::$postType
            ));

            // Create WordPress Pagination
            $args['perPage']     = self::ITEM_PER_PAGE;
            $args['currentPage'] = Admin_Template::getCurrentPaged();
            $args['pagination']  = '';

            if ($args['total'] > 0) {
                $args['pagination'] = Admin_Template::paginate_links(array(
                    'item_per_page' => self::ITEM_PER_PAGE,
                    'total'         => $args['total'],
                    'echo'          => false
                ));
            }

            // Show Template Page
            Admin_Template::get_template(array('layout/header', 'layout/tabbed-page-header', 'pages/pages', 'layout/postbox.hide', 'layout/footer'), $args);
        }
    }

    /**
     * @throws \Exception
     */
    public static function custom_page_statistics()
    {
        global $wpdb;

        // Page ID
        $ID     = sanitize_text_field($_GET['ID']);
        $Type   = sanitize_text_field($_GET['type']);
        $PageID = !empty($_GET['page_id']) ? sanitize_text_field($_GET['page_id']) : false;

        // Page title
        $args['title'] = __('Statistics for Individual Pages', 'wp-statistics');

        // Get Current Page Url
        $args['pageName']   = Menus::get_page_slug('pages');
        $args['custom_get'] = array(
            'ID'      => $ID,
            'type'    => $Type,
            'page_id' => $PageID,
        );

        // Get Date-Range
        $args['DateRang']    = Admin_Template::DateRange();
        $args['HasDateRang'] = True;

        // List Of Pages From custom Type
        $args['list'] = array();

        // Check Is Post Or Term
        $_is_post   = in_array($Type, array("home", "page", "post", "product", "attachment"));
        $_post_type = (strpos($Type, 'post_type_') !== false) ? str_replace('post_type_', '', $Type) : $Type;
        $_is_post   = ($_is_post == false) ? in_array($_post_type, self::$postTypes) : $_is_post;
        $_is_term   = in_array($Type, array("category", "post_tag", "tax"));

        if ($_is_post === true || $_is_term === true) {
            $query = $wpdb->get_results(
                $wpdb->prepare("SELECT `id`, SUM(count) as total FROM `" . DB::table('pages') . "` WHERE `type` = %s GROUP BY `id` ORDER BY `total` DESC LIMIT 0,100", $Type),
                ARRAY_A
            );
        }

        // Create Select List For WordPress Posts
        if ($_is_post and isset($query)) {
            $args['list'][$ID] = get_the_title($ID);

            foreach ($query as $item) {
                $get_page_info = Pages::get_page_info($item['id'], $Type);

                if (isset($get_page_info['title']) and !empty($get_page_info['title']) and $item['id'] != $ID) {
                    $args['list'][$item['id']] = $get_page_info['title'];
                }
            }
        }

        $subList      = [];
        $subListQuery = $wpdb->get_results(
            $wpdb->prepare("SELECT `uri`, `page_id`, SUM(count) as total FROM `" . DB::table('pages') . "` WHERE `id` = %s AND `type` = %s GROUP BY `uri` ORDER BY `total` DESC LIMIT 0,100", $ID, $Type),
            ARRAY_A
        );

        foreach ($subListQuery as $item) {
            $subList[$item['page_id']] = $item['uri'];
        }

        $args['sub_list'] = $subList;

        // Create Select List For WordPress Terms
        if ($_is_term and isset($query)) {
            $this_term         = Pages::get_page_info($ID, $Type);
            $args['list'][$ID] = $this_term['title'];

            foreach ($query as $item) {
                $get_page_info = Pages::get_page_info($item['id'], $Type);

                if (isset($get_page_info['title']) and strlen($get_page_info['title']) > 2 and $item['id'] != $ID) {
                    $args['list'][$item['id']] = $get_page_info['title'];
                }
            }
        }

        // Load Single Page Components
        foreach (self::SINGLE_PAGE_COMPONENTS as $component) {
            $args[$component] = apply_filters(
                'wp_statistics_pages_chart_' . $component,
                Admin_Template::get_template(array('meta-box/pages-' . $component . '-preview'), null, true),
                $args
            );
        }

        // Show Template Page
        Admin_Template::get_template(array('layout/header', 'layout/title', 'layout/select', 'pages/page-chart', 'layout/postbox.hide', 'layout/footer'), $args);
    }
}

pages_page::instance();
