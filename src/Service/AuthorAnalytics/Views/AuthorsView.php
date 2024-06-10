<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Views;

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\BaseView;
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
        $args = [
            'date'      => [
                'from'  => isset($_GET['from']) ? sanitize_text_field($_GET['from']) : date('Y-m-d', strtotime('-1 month')),
                'to'    => isset($_GET['to']) ? sanitize_text_field($_GET['to']) : date('Y-m-d'),
            ],
            'post_type' => isset($_GET['pt']) ? sanitize_text_field($_GET['pt']) : 'post',
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged()
        ];

        if (isset($_GET['order_by'])) {
            $args['order_by'] = sanitize_text_field($_GET['order_by']);
        } 
        
        if (isset($_GET['order'])) {
            $args['order'] = sanitize_text_field($_GET['order']);
        }

        $authorAnalyticsData  = new AuthorAnalyticsDataProvider($args);
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