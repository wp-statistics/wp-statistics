<?php

namespace WP_Statistics\Service\Admin\FilterHandler;

use WP_STATISTICS\Country;
use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\Referred;
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Query;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Url;

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

            $queryString = Request::get('queryString', '');
            $filters     = Request::getParams(['filters']);

            if (empty($filters)) {
                wp_send_json([]);
            }

            $filters = $filters['filters'];

            $output = [];

            foreach ($filters as $filter) {
                if (method_exists($this,  $filter)) {
                    $output[$filter] = $this->$filter($queryString);
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

            $source      = Request::get('source', '');
            $paged       = Request::get('paged', 1, 'number');
            $postType    = Request::get('post_type', array_values(Helper::get_list_post_type()));
            $authorId    = Request::get('author_id', '', 'number');
            $page        = Request::get('page');
            $queryString = Request::get('queryString', '');

            $search = Request::get('search', '');
            $search = Url::cleanUrl($search);

            $output = [];

            if (method_exists($this,  $source)) {
                $output = call_user_func([$this, $source], $search, $paged, $page, $postType, $authorId, $queryString);
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

        $result = Query::select([
            'agent',
        ])
            ->from('visitor')
            ->whereNotNull('agent')
            ->groupBy(['agent'])
            ->getAll();

        $result ? $result : [];

        foreach ($result as $key => $browser) {
            $name = $browser->agent;
            $key  = strtolower($name);
            $key  = str_replace(' ', '_', $key);

            $args[$name] = $name;
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
        $args         = [];
        $country_list = Country::getList();

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

        $result = Query::select([
            'platform',
        ])
            ->from('visitor')
            ->whereNotNull('platform')
            ->groupBy(['platform'])
            ->getAll();

        $result ? $result : [];

        foreach ($result as $key => $platform) {
            $name = $platform->platform;
            $key  = strtolower($name);

            $args[$key] = $name;
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
     * Retrieves a list of source channel.
     *
     * @return array
     */
    public function sourceChannel() {
        $channels   = SourceChannels::getList();
        unset($channels['direct']);

        return $channels;
    }

    public function getUser($search) {
        global $wpdb;

        $args = [];

        // Base query
        $query = "SELECT visitors.user_id, users.user_login, users.user_email
                  FROM `" . DB::table('visitor') . "` AS visitors
                  JOIN `" . $wpdb->users . "` AS users
                  ON visitors.user_id = users.ID
                  WHERE visitors.user_id > 0";

        // If search term is provided, filter by email or username
        if (!empty($search)) {
            $search = '%' . $wpdb->esc_like($search) . '%';
            $query .= " AND (users.user_login LIKE %s OR users.user_email LIKE %s)";
        }

        $query .= " GROUP BY visitors.user_id ORDER BY visitors.user_id DESC;";

        // Prepare and execute the query
        if (!empty($search)) {
            $query = $wpdb->prepare($query, $search, $search);
        }

        $results = $wpdb->get_results($query, ARRAY_A);

        foreach ($results as $user) {
            $option = [
                'id'   => $user['user_id'],
                'text' => $user['user_login'] . " #" . $user['user_id'] . ""
            ];

            $args[] = $option;
        }

        return $args;
    }

    /**
     * Retrieves a list of the users with posts.
     *
     * @return array
     */
    public function getUserWithPosts($search)
    {
        $args = [];

        // Query arguments
        $query_args = [
            'has_published_posts' => true,
            'number'              => 10,
        ];

        // If a search term is provided, add it to the query
        if (!empty($search)) {
            $query_args['search'] = '*' . esc_attr($search) . '*';
            $query_args['search_columns'] = ['display_name', 'user_login', 'user_email'];
        }

        $users = get_users($query_args);

        foreach ($users as $key => $user) {
            $option = [
                'id'   => esc_html($user->ID),
                'text' => esc_html($user->display_name) . " #" . esc_html($user->ID) . ""
            ];

            $args[] = $option;
        }

        return $args;
    }

    /**
     * Retrieves a list of the urls based on search criteria.
     *
     * @param string $search The search string for URL filtering.
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
     * @param string $search The search string for referrers.
     * @return array
     */
    public function getReferrer($search)
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
    public function getPostTypes($page)
    {
        $currentPage = admin_url("admin.php{$page}");

        $queryKey  = 'pt';
        $baseUrl   = htmlspecialchars_decode(esc_url(remove_query_arg(['pt', 'pid'], $currentPage)));
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
            'selectedOption' => Request::get($queryKey),
            'lockCustomPostTypes' => !Helper::isAddOnActive('data-plus'),
        ];
    }

    /**
     * Retrieves a list of authors and generates corresponding data.
     *
     * @return array
     */
    public function getAuthor($page)
    {
        $currentPage = admin_url("admin.php{$page}");

        $queryKey = 'author_id';
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg([$queryKey, 'pid'], $currentPage)));
        $authors  = get_users(['has_published_posts' => true]);

        $args = [];

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
            'selectedOption' => Request::get($queryKey),
        ];
    }

    /**
     * Retrieves a list of taxonomies and generates corresponding data.
     *
     * @return array
     */
    public function getTaxonomies($page)
    {
        $currentPage = admin_url("admin.php{$page}");

        $queryKey   = 'tx';
        $taxonomies = Helper::get_list_taxonomy(true);
        $baseUrl    = htmlspecialchars_decode(esc_url(remove_query_arg([$queryKey, 'pid'], $currentPage)));

        $args = [];

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
            'selectedOption' => Request::get($queryKey, 'category'),
        ];
    }

    /**
     * Retrieves filtered search channels and generates corresponding data.
     *
     * @return array
     */
    public function getSearchChannels($page)
    {
        $currentPage = admin_url("admin.php{$page}");

        $channels = Helper::filterArrayByKeys(SourceChannels::getList(), ['search', 'paid_search']);
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'], $currentPage)));

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
            'selectedOption' => Request::get('source_channel'),
        ];
    }

    /**
     * Retrieves filtered social channels and generates corresponding data.
     *
     * @return array
     */
    public function getSocialChannels($page)
    {
        $currentPage = admin_url("admin.php{$page}");

        $channels = Helper::filterArrayByKeys(SourceChannels::getList(), ['social', 'paid_social']);
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'], $currentPage)));

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
            'selectedOption' => Request::get('source_channel'),
        ];
    }

    /**
     * Retrieves filtered source channels and generates corresponding data.
     *
     * @return array
     */
    public function getSourceChannels($page)
    {
        $currentPage = admin_url("admin.php{$page}");

        $channels = SourceChannels::getList();
        unset($channels['direct']);

        $baseUrl = htmlspecialchars_decode(esc_url(remove_query_arg(['source_channel', 'pid'], $currentPage)));

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
            'selectedOption' => Request::get('source_channel')
        ];
    }

    /**
     * Retrieves a list of user roles and generates corresponding data.
     *
     * @return array
     */
    public function getUserRoles($page)
    {
        $currentPage = admin_url("admin.php{$page}");

        $queryKey = 'role';
        $roles    = wp_roles()->role_names;
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg([$queryKey],$currentPage)));

        $args = [];

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
            'selectedOption' => Request::get($queryKey, '')
        ];
    }

    /**
     * Retrieves query parameters for a specific post and generates corresponding data.
     *
     * @return array
     */
    public function getQueryParameters($page)
    {
        $currentPage = admin_url("admin.php{$page}");

        $queryKey = 'qp';
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg([$queryKey], $currentPage)));
        $postId   = Request::get('post_id', '', 'number');

        $viewsModel = new ViewsModel();
        $parameters = $viewsModel->getViewedPageUri(['id' => $postId]);
        $pageSlug   = get_page_uri($postId);

        $args = [];

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
            'selectedOption' => Request::get($queryKey)
        ];
    }

    /**
     * Fetches a list of posts matching the provided search term, post type, and author filter.
     *
     * @param string $search      The search keyword to filter posts.
     * @param int    $paged       The current page number for pagination.
     * @param mixed  $page        A flag or identifier indicating if a query should be executed.
     * @param string $postType    The type of post to query.
     * @param int    $authorId    The ID of the author to filter posts.
     * @param string $queryString Current page query string.
     * @return array
     */
    public function getPage($search, $paged, $page, $postType, $authorId, $queryString)
    {
        if (empty($page)) {
            return [];
        }

        $currentPage = admin_url("admin.php{$queryString}");

        $queryKey = 'pid';
        $baseUrl  = htmlspecialchars_decode(esc_url(remove_query_arg([$queryKey], $currentPage)));

        $query = new \WP_Query([
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            'paged'          => $paged,
            'post_type'      => $postType,
            'author'         => $authorId,
            's'              => $search
        ]);

        $args = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $option = [
                    'id'   => add_query_arg(['pid' => get_the_ID()], $baseUrl),
                    'text' => get_the_title()
                ];

                $args[] = $option;
            }
        }

        return $args;
    }

    public function getPageId($search)
    {
        $query = new \WP_Query([
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            's'              => $search
        ]);

        $args = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $option = [
                    'id'    => get_the_ID(),
                    'text'  => get_the_title()
                ];

                $args[] = $option;
            }
        }

        return $args;
    }
}
