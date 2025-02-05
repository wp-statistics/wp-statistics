<?php

namespace WP_Statistics\Service\Admin\FilterHandler;

use WP_STATISTICS\Country;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\Referred;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Query;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Url;
use WP_STATISTICS\Visitor;

/**
 * Class FilterManager
 *
 * Manages filters for WP Statistics plugin, including AJAX filter handling
 * and various predefined filters such as browsers, locations, etc.
 */
class FilterManager
{
    /**
     * Constructor
     * Registers the AJAX filter handlers.
     */
    public function __construct()
    {
        add_filter('wp_statistics_ajax_list', [$this, 'addFilterAjax']);
    }

    /**
     * Adds AJAX filter actions to the WordPress statistics list.
     *
     * @param array $list The existing list of AJAX actions.
     * @return array
     */
    public function addFilterAjax($list)
    {
        $list[] = [
            'class'  => $this,
            'action' => 'get_filters',
            'public' => false
        ];

        $list[] = [
            'class'  => $this,
            'action' => 'search_filter',
            'public' => false
        ];

        return $list;
    }

    /**
     * Handles AJAX request for retrieving filters.
     *
     * Sends JSON response with available filters based on the request parameters.
     */
    public function get_filters_action_callback()
    {

        if (Helper::is_request('ajax') and isset($_REQUEST['page'])) {

            // Check Refer Ajax
            check_ajax_referer('wp_rest', 'wps_nonce');


            $filters = Request::getParams(['filters']);

            if (empty($filters)) {
                wp_send_json([]);
            }

            $filters = $filters['filters'];

            $output = [];

            foreach ($filters as $filter) {
                if (method_exists($this,  $filter)) {
                    $output[$filter] = $this->$filter();
                }
            }

            wp_send_json($output);
        }
        exit;
    }

    /**
     * Handles AJAX search requests for filtering URLs.
     *
     * @return void
     */
    public function search_filter_action_callback()
    {
        if (Request::isFrom('ajax') && User::Access('read')) {
            check_ajax_referer('wp_rest', 'wps_nonce');

            $source  = Request::get('source', '');
            $search  = Request::get('search', '');
            $search  = Url::cleanUrl($search);

            $output = [];

            if (method_exists($this,  $source)) {
                $output = call_user_func([$this, $source], $search);
            }

            wp_send_json($output);
        }

        exit;
    }

    /**
     * Retrieves a list of available web browsers.
     *
     * @return array
     */
    public function browsers()
    {
        $args     = [];
        $browsers = DeviceHelper::getBrowserList();

        foreach ($browsers as $key => $se) {
            $args[$key] = $se;
        }

        return $args;
    }

    /**
     * Retrieves a list of available countries.
     *
     * @return array
     */
    public function location()
    {
        $args               = [];
        $country_list       = Country::getList();

        foreach ($country_list as $key => $name) {
            $args[$key] = $name;
        }

        $first_key = key($args);
        $first_val = $args[$first_key];
        unset($args[$first_key]);
        $args[$first_key] = $first_val;

        return $args;
    }

    /**
     * Retrieves a list of available platforms.
     *
     * @return array
     */
    public function platform()
    {
        $args = [];
        $platforms_list = DeviceHelper::getPlatformsList();

        foreach ($platforms_list as $key => $platform) {
            $args[$key] = $platform;
        }

        return $args;
    }

    /**
     * Retrieves a list of referring domains.
     *
     * @return array
     */
    public function referrer()
    {
        $args = [];

        $referrer_list      = Referred::getList(array('min' => 50, 'limit' => 300));
        foreach ($referrer_list as $site) {
            $args[$site->domain] = $site->domain;
        }

        return $args;
    }

    /**
     * Retrieves a list of users who have visited the site.
     *
     * @return array
     */
    public function users()
    {
        $args = [];
        $user_list       = Visitor::get_users_visitor();
        foreach ($user_list as $user_id => $user_inf) {
            $args[$user_id] = $user_inf['user_login'] . " #" . $user_id . "";
        }

        return $args;
    }

    /**
     * Retrieves a list of the users with posts.
     *
     * @return array
     */
    public function usersWithPosts()
    {
        $args  = [];
        $users = get_users(['has_published_posts' => true]);

        foreach ($users as $key => $user) {
            $args[esc_html($user->ID)] = esc_html($user->display_name) . ' #' . esc_html($user->ID);
        }

        return $args;
    }

    /**
     * Retrieves a list of the urls based on search criteria.
     *
     * @return array
     */
    public function url($search)
    {
        $args = [];

        $pages = Query::select(['DISTINCT uri'])
            ->from('pages')
            ->where('uri', 'LIKE', "%{$search}%")
            ->getAll();

        foreach ($pages as $key => $page) {
            if (empty($page->uri)) {
                continue;
            }

            $option = [
                'id'   => $page->uri,
                'text' => $page->uri
            ];

            $args[] = $option;
        }

        return $args;
    }

    /**
     * Retrieves a list of the referrers based on search criteria.
     *
     * @return array
     */
    public function referrers($search)
    {
        $args = [];

        $visitorsModel = new VisitorsModel();
        $referrers  = $visitorsModel->getReferrers([
            'referrer'      => $search,
            'decorate'      => true
        ]);

        foreach ($referrers as $referrer) {
            $option = [
                'id'   => $referrer->getRawReferrer(),
                'text' => $referrer->getRawReferrer()
            ];

            $args[] = $option;
        }

        return $args;
    }

    /**
     * Retrieves post types with their details and generates corresponding data.
     * 
     * @return array
     */
    public function getPostTypes()
    {
        $queryKey            = 'pt';
        $baseUrl = htmlspecialchars_decode(esc_url(remove_query_arg(['pt', 'pid'], wp_get_referer())));
        $postTypes = Helper::get_list_post_type();

        $args = [];

        foreach ($postTypes as $postType) {
            $args[] = [
                'slug' => esc_html($postType),
                'name' => esc_html(Helper::getPostTypeName($postType)),
                'url' => add_query_arg([$queryKey => $postType], $baseUrl),
                'premium' => Helper::isCustomPostType($postType) && !Helper::isAddOnActive('data-plus')
            ];
        }

        return [
            'args' => $args,
            'baseUrl' => $baseUrl,
            'selectedOptions' => Request::get($queryKey),
            'lockCustomPostTypes' => !Helper::isAddOnActive('data-plus'),
        ];
    }

    /**
     * Retrieves a list of authors and generates corresponding data.
     * 
     * @return array
     */
    public function getAuthor()
    {
        $queryKey = 'author_id';
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg([$queryKey, 'pid'], wp_get_referer())));
        $authors  = get_users(['has_published_posts' => true]);

        foreach ($authors as $author) {
            $args[] = [
                'slug' => esc_attr($author->ID),
                'name' => esc_html($author->display_name),
                'url' => add_query_arg([$queryKey => $author->ID], $baseUrl),
            ];
        }

        return [
            'args' => $args,
            'baseUrl' => $baseUrl,
            'selectedOptions' => Request::get($queryKey),
        ];
    }

    /**
     * Retrieves a list of taxonomies and generates corresponding data.
     * 
     * @return array
     */
    public function getTaxonomies()
    {
        $queryKey   = 'tx';
        $taxonomies = Helper::get_list_taxonomy(true);
        $baseUrl    = htmlspecialchars_decode(esc_url(remove_query_arg([$queryKey, 'pid'], wp_get_referer())));

        foreach ($taxonomies as $key => $name) {
            $args[] = [
                'slug' => esc_attr($key),
                'name' => esc_html(ucwords($name)),
                'url' => add_query_arg([$queryKey => $key], $baseUrl),
            ];
        }

        return [
            'args' => $args,
            'baseUrl' => $baseUrl,
            'selectedOptions' => Request::get($queryKey, 'category'),
        ];
    }

    /**
     * Retrieves filtered search channels and generates corresponding data.
     * 
     * @return array
     */
    public function getSearchChannels()
    {
        $channels = Helper::filterArrayByKeys(SourceChannels::getList(), ['search', 'paid_search']);
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'], wp_get_referer())));

        foreach ($channels as $key => $channel) {
            $args[] = [
                'slug' => esc_attr($key),
                'name' => esc_html($channel),
                'url' => add_query_arg(['source_channel' => $key], $baseUrl),
            ];
        }

        return [
            'args' => $args,
            'baseUrl' => $baseUrl,
            'selectedOptions' => Request::get('source_channel'),
        ];
    }

    /**
     * Retrieves filtered social channels and generates corresponding data.
     * 
     * @return array
     */
    public function getSocialChannels()
    {
        $channels = Helper::filterArrayByKeys(SourceChannels::getList(), ['social', 'paid_social']);
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'], wp_get_referer())));

        foreach ($channels as $key => $channel) {
            $args[] = [
                'slug' => esc_attr($key),
                'name' => esc_html($channel),
                'url' => add_query_arg(['source_channel' => $key], $baseUrl),
            ];
        }

        return [
            'args' => $args,
            'baseUrl' => $baseUrl,
            'selectedOptions' => Request::get('source_channel'),
        ];
    }

    /**
     * Retrieves filtered source channels and generates corresponding data.
     * 
     * @return array
     */
    public function getSourceChannels()
    {
        $channels = SourceChannels::getList();
        unset($channels['direct']);

        $baseUrl = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'], wp_get_referer())));

        foreach ($channels as $key => $channel) {
            $args[] = [
                'slug' => esc_attr($key),
                'name' => esc_html($channel),
                'url' => add_query_arg(['source_channel' => $key], $baseUrl),
            ];
        }

        return [
            'args' => $args,
            'baseUrl' => $baseUrl,
            'selectedOptions' => Request::get('source_channel')
        ];
    }

    /**
     * Retrieves a list of user roles and generates corresponding data.
     * 
     * @return array
     */
    public function getUserRoles()
    {
        $queryKey = 'role';
        $roles    = wp_roles()->role_names;
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg([$queryKey], wp_get_referer())));

        foreach ($roles as $key => $role) {
            $args[] = [
                'slug' => esc_attr($key),
                'name' => esc_html($role),
                'url' => add_query_arg([$queryKey => $key], $baseUrl),
            ];
        }

        return [
            'args' => $args,
            'baseUrl' => $baseUrl,
            'selectedOptions' => Request::get($queryKey, '')
        ];
    }

    /**
     * Retrieves query parameters for a specific post and generates corresponding data.
     * 
     * @return array
     */
    public function getQueryParameters()
    {
        $queryKey = 'qp';
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg([$queryKey], wp_get_referer())));
        $postId   = Request::get('post_id', '', 'number');

        $viewsModel = new ViewsModel();
        $parameters = $viewsModel->getViewedPageUri(['id' => $postId]);
        $pageSlug   = get_page_uri($postId);

        foreach ($parameters as $key => $parameter) {
            $title = preg_replace('/^.*' . preg_quote($pageSlug, '/') . '/', '', $parameter->uri);

            $args[] = [
                'slug' => esc_attr($parameter->page_id),
                'name' => !empty($title) ? esc_html($title) : esc_html($parameter->uri),
                'url' => add_query_arg([$queryKey => $parameter->page_id], $baseUrl)
            ];
        }

        return [
            'args' => $args,
            'baseUrl' => $baseUrl,
            'selectedOptions' => Request::get($queryKey)
        ];
    }
}
