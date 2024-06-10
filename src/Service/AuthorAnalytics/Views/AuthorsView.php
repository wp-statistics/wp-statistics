<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Views;

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Service\AuthorAnalytics\AuthorAnalyticsDataProvider;

class AuthorsView extends BaseView
{
    /**
     * Get author report data
     * 
     * @return array
     */
    public function getData()
    {
        $from       = Request::get('from', date('Y-m-d', strtotime('-1 month')));
        $to         = Request::get('to', date('Y-m-d'));
        $postType   = Request::get('pt', 'post');
        $orderBy    = Request::get('order_by');
        $order      = Request::get('order', 'DESC');

        $args = [
            'date'      => ['from' => $from, 'to' => $to],
            'post_type' => $postType,
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged()
        ];

        if ($orderBy) {
            $args['order_by']   = $orderBy;
            $args['order']      = $order;
        }

        $authorAnalyticsData = new AuthorAnalyticsDataProvider($args);
        return $authorAnalyticsData->getAuthorsReportData();
    }

    public function render()
    {
        $data = $this->getData();

        $args = [
            'title'         => esc_html__('Authors', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug('author-analytics'),
            'DateRang'      => Admin_Template::DateRange(),
            'custom_get'    => ['type' => 'authors', 'pt' => Request::get('pt', 'post')],
            'HasDateRang'   => true,
            'filters'       => ['post-type'],
            'backUrl'       => Menus::admin_url('author-analytics'),
            'backTitle'     => esc_html__('Authors Performance', 'wp-statistics'),
            'data'          => $data['authors'],
            'paged'         => Admin_Template::getCurrentPaged(),
        ];

        if ($data['total'] > 0) {
            $args['total'] = $data['total'];

            $args['pagination'] = Admin_Template::paginate_links([
                'total' => $data['total'],
                'echo'  => false
            ]);
        }

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/authors-report', 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}