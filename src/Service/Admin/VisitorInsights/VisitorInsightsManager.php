<?php

namespace WP_Statistics\Service\Admin\VisitorInsights;

use WP_STATISTICS\Helper;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Query;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Url;

class VisitorInsightsManager
{

    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
        add_filter('wp_statistics_ajax_list', [$this, 'registerAjaxActions']);
    }

    /**
     * Add menu item
     *
     * @param array $items
     * @return array
     */
    public function addMenuItem($items)
    {
        $items['visitor_insights'] = [
            'sub'       => 'overview',
            'title'     => esc_html__('Visitor Insights', 'wp-statistics'),
            'page_url'  => 'visitors',
            'callback'  => VisitorInsightsPage::class,
            'priority'  => 25,
        ];

        return $items;
    }

    /**
     * Registers AJAX actions for URL filter.
     *
     * @param array $list
     *
     * @return array List of AJAX actions.
     */
    public function registerAjaxActions($list)
    {
        $list[] = [
            'class'     => $this,
            'action'    => 'search_url',
            'public'    => false
        ];

        return $list;
    }

    /**
     * Handles the AJAX action for searching urls in pages table.
     *
     * @return void
     */
    public function search_url_action_callback()
    {
        if (Request::isFrom('ajax') && User::Access('read')) {
            check_ajax_referer('wp_rest', 'wps_nonce');

            $results = [];
            $search  = Request::get('search', '');
            $search  = Url::cleanUrl($search);

            $postTypes = Helper::get_updated_list_post_type();
            $postTypes[] = 'home';

            $pages = Query::select(['DISTINCT uri'])
                ->from('pages')
                ->where('uri', 'LIKE', "%{$search}%")
                ->where('type', 'IN', $postTypes)
                ->getAll();

            foreach ($pages as $key => $page) {
                if (empty($page->uri)) {
                    continue;
                }

                $option = [
                    'id'   => $page->uri,
                    'text' => $page->uri
                ];

                $results[] = $option;
            }

            wp_send_json(['results' => $results]);
        }

        exit;
    }
}
