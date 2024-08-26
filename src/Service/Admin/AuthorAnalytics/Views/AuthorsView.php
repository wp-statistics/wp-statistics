<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\AuthorAnalytics\AuthorAnalyticsDataProvider;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Utils\Request;

class AuthorsView extends BaseView
{
    /**
     * Get author report data
     *
     * @return array
     */
    public function getData()
    {
        $postType = Request::get('pt', 'post');
        $orderBy  = Request::get('order_by');
        $order    = Request::get('order', 'DESC');

        $args = [
            'post_type' => $postType,
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged()
        ];

        if ($orderBy) {
            $args['order_by'] = $orderBy;
            $args['order']    = $order;
        }

        $authorAnalyticsData = new AuthorAnalyticsDataProvider($args);
        return $authorAnalyticsData->getAuthorsReportData();
    }

    public function render()
    {
        try {
            $postType   = Request::get('pt', 'post');
            $data       = $this->getData();
            $template   = 'authors-report';

            if (!Helper::isAddOnActive('data-plus') && Helper::isCustomPostType($postType)) {
                $template = 'authors-report-locked';
            }

            $args = [
                'title'       => esc_html__('Authors', 'wp-statistics'),
                'pageName'    => Menus::get_page_slug('author-analytics'),
                'DateRang'    => Admin_Template::DateRange(),
                'custom_get'  => [
                    'type'      => 'authors', 
                    'pt'        => Request::get('pt', 'post'),
                    'order_by'  => Request::get('order_by', 'total_views'),
                    'order'     => Request::get('order', 'desc'),
                ],
                'hasDateRang' => true,
                'filters'     => ['post-type'],
                'backUrl'     => Menus::admin_url('author-analytics'),
                'backTitle'   => esc_html__('Authors Performance', 'wp-statistics'),
                'data'        => $data['authors'],
                'paged'       => Admin_Template::getCurrentPaged(),
            ];

            if ($data['total'] > 0) {
                $args['total'] = $data['total'];

                $args['pagination'] = Admin_Template::paginate_links([
                    'total' => $data['total'],
                    'echo'  => false
                ]);
            }

            Admin_Template::get_template(['layout/header', 'layout/title', "pages/author-analytics/$template", 'layout/postbox.toggle', 'layout/footer'], $args);
        } catch (\Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}