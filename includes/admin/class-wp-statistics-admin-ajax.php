<?php

namespace WP_STATISTICS;

use WP_Statistics\Components\DateRange;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Geolocation\Provider\DbIpProvider;
use WP_Statistics\Service\Geolocation\Provider\MaxmindGeoIPProvider;
use WP_Statistics\Utils\Request;

/**
 * @deprecated Use WP_Statistics\AjaxHandler\AjaxManager instead for global AJAX callbacks.
 *
 * @todo Refactor this class and move ajax action to their services or global AjaxManager file
 */
class Ajax
{
    public function __construct()
    {
        add_action('init', [$this, 'registerAjaxCallbacks']);
    }

    /**
     * Register AJAX callbacks
     */
    public function registerAjaxCallbacks()
    {
        /**
         * List Of Setup Ajax request in WordPress
         */
        $list = [
            [
                'class'  => $this,
                'action' => 'update_geoip_database',
                'public' => false
            ],
            [
                'class'  => $this,
                'action' => 'admin_meta_box',
                'public' => false
            ],
            [
                'class'  => $this,
                'action' => 'get_page_filter_items',
                'public' => false
            ],
            [
                'class'  => $this,
                'action' => 'search_visitors',
                'public' => false
            ],
            [
                'class'  => $this,
                'action' => 'store_date_range',
                'public' => false
            ],
            [
                'class'  => $this,
                'action' => 'dismiss_notices',
                'public' => false
            ]
        ];

        /**
         * @deprecated: wp_statistics_ajax_list hook was deprecated since v14.16.
         * Use AjaxManager class instead to define global AJAX callbacks.
        */
        $list = apply_filters('wp_statistics_ajax_list', $list);

        foreach ($list as $item) {
            $class    = $item['class'];
            $action   = $item['action'];
            $callback = $action . '_action_callback';
            $isPublic = isset($item['public']) && $item['public'] == true ? true : false;

            // If callback exists in the class, register the action
            if (!empty($class) && method_exists($class, $callback)) {
                add_action('wp_ajax_wp_statistics_' . $action, [$class, $callback]);

                // Register the AJAX callback publicly
                if ($isPublic) {
                    add_action('wp_ajax_nopriv_wp_statistics_' . $action, [$class, $callback]);
                }
            }
        }
    }

    /**
     * Checks if the current request is allowed to perform this action.
     *
     * This method ensures that:
     * 1. The request is an AJAX request.
     * 2. The user has the required access permission (passed as $requiredPermission).
     * 3. The AJAX nonce is valid.
     *
     * If any of these checks fail, it immediately returns a JSON error response
     * and exits the script.
     *
     * @param string $requiredPermission The capability required to access this action (default: 'manage').
     * @param string $nonceAction Optional. The nonce action to verify (default: 'wp_rest').
     * @param string $nonceName Optional. The nonce field name (default: 'wps_nonce').
     *
     * @return void
     */
    private function checkAccess($requiredPermission = 'manage', $nonceAction = 'wp_rest', $nonceName = 'wps_nonce')
    {
        if (!Request::isFrom('ajax') || !User::Access($requiredPermission)) {
            wp_send_json_error([
                'message' => esc_html__('Unauthorized.', 'wp-statistics')
            ]);
        }

        $nonceValid = check_ajax_referer($nonceAction, $nonceName, false);
        if (!$nonceValid) {
            wp_send_json_error([
                'message' => esc_html__('Invalid nonce.', 'wp-statistics')
            ]);
        }
    }


    /**
     * Setup an AJAX action to update geoIP database.
     */
    public function update_geoip_database_action_callback()
    {

        if (Helper::is_request('ajax') and User::Access('manage')) {
            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');


            $method   = Request::get('geoip_location_detection_method', 'maxmind');
            $provider = MaxmindGeoIPProvider::class;

            if ('dbip' === $method) {
                $provider = DbIpProvider::class;
            }

            $result = GeolocationFactory::downloadDatabase($provider);

            if (is_wp_error($result)) {
                esc_html_e($result->get_error_message());
            } else {
                esc_html_e('GeoIP Database successfully updated.', 'wp-statistics');
            }
        } else {
            esc_html_e('Unauthorized access!', 'wp-statistics');
        }

        exit;
    }

    /**
     * Setup Admin Meta box render ajax
     */
    public function admin_meta_box_action_callback()
    {
        if (Helper::is_request('ajax') and User::Access('read')) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');

            $metaboxName = sanitize_text_field($_GET['name']);

            // Check Exist MetaBox Name
            if (in_array($metaboxName, array_keys(Meta_Box::getList())) and Meta_Box::metaBoxClassExist($metaboxName)) {

                $parameters = [];
                foreach ($_GET as $key => $value) {
                    if ($value && !in_array($key, ['action', 'wps_nonce', '_'])) {
                        $parameters[$key] = sanitize_text_field($value);
                    }
                }

                $class = Meta_Box::getMetaBoxClass($metaboxName);

                wp_send_json($class::get($parameters));

            } else {
                wp_send_json(array('code' => 'not_found_meta_box', 'message' => __('Invalid MetaBox Name in Request.', 'wp-statistics')), 400);
            }
        }

        exit;
    }

    /**
     * Get page filter items
     */
    public function get_page_filter_items_action_callback()
    {
        if (Helper::is_request('ajax') and User::Access('read')) {

            check_ajax_referer('wp_rest', 'wps_nonce');

            $paged         = Request::get('paged', 1, 'number');
            $postType      = Request::get('post_type', array_values(Helper::get_list_post_type()));
            $authorId      = Request::get('author_id', '', 'number');
            $search        = Request::get('search', '');
            $page          = Request::get('page');
            $selectedPost  = Request::get('post_id', false, 'number');
            $hideAllOption = Request::get('hide_all_option', false);

            if (!$page) {
                wp_send_json([
                    'code'    => 'not_found_page',
                    'message' => esc_html__('Invalid Page in Request.', 'wp-statistics')
                ], 400);
            }

            $query = new \WP_Query([
                'post_status'    => 'publish',
                'posts_per_page' => 10,
                'paged'          => $paged,
                'post_type'      => $postType,
                'author'         => $authorId,
                's'              => $search
            ]);

            $posts = [];
            if ($query->have_posts()) {
                if (empty($hideAllOption) && $paged == 1 && empty($search)) {
                    $allOption = [
                        'id'   => Menus::admin_url($page),
                        'text' => esc_html__('All', 'wp-statistics')
                    ];

                    if (!$selectedPost) {
                        $allOption['selected'] = true;
                    }

                    $posts[] = $allOption;
                }

                while ($query->have_posts()) {
                    $query->the_post();

                    $option = [
                        'id'   => get_the_ID(),
                        'text' => get_the_title()
                    ];

                    if ($selectedPost == get_the_ID()) {
                        $option['selected'] = true;
                    }

                    $posts[] = $option;
                }
            }

            wp_send_json([
                'results'    => $posts,
                'pagination' => [
                    'more' => $query->max_num_pages > $paged ? true : false
                ]
            ]);
        }

        exit;
    }

    public function search_visitors_action_callback()
    {
        if (Helper::is_request('ajax') and User::Access('read')) {

            check_ajax_referer('wp_rest', 'wps_nonce');

            $results = [];
            $search  = Request::get('search', '');

            $visitorsModel = new VisitorsModel();
            $visitors      = $visitorsModel->searchVisitors([
                'ip'       => $search,
                'username' => $search,
                'email'    => $search
            ]);

            foreach ($visitors as $visitor) {
                $option = [
                    'id'   => Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->ID]),
                    'text' => sprintf(esc_html__('Visitor (#%s)', 'wp-statistics'), $visitor->ID)
                ];

                $results[] = $option;
            }

            wp_send_json(['results' => $results]);
        }

        exit;
    }

    public function store_date_range_action_callback()
    {
        if (Helper::is_request('ajax')) {
            check_ajax_referer('wp_rest', 'wps_nonce');

            $date = Request::get('date', [], 'array');
            DateRange::store($date);

            wp_send_json_success(['message' => esc_html__('Date range has been stored successfully.', 'wp-statistics')]);

        }

        exit;
    }

    public function dismiss_notices_action_callback()
    {
        if (!Request::isFrom('ajax')) exit;

        check_ajax_referer('wp_rest', 'wps_nonce');

        $noticeId = Request::get('notice_id');

        if (!empty($noticeId)) {
            $dismissedNotices = get_option('wp_statistics_dismissed_notices', []);

            if (!in_array($noticeId, $dismissedNotices)) {
                $dismissedNotices[] = $noticeId;
                update_option('wp_statistics_dismissed_notices', $dismissedNotices);
            }

            wp_send_json_success(['message' => esc_html__('Notice dismissed.', 'wp-statistics')]);
        } else {
            wp_send_json_error(['message' => esc_html__('Invalid Notice ID.', 'wp-statistics')]);
        }

        exit;
    }
}

new Ajax;
