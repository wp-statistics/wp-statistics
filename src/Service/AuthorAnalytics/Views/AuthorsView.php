<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Views;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Service\AuthorAnalytics\AuthorAnalyticsData;

class AuthorsView
{
    /**
     * Get author report data
     * 
     * @return array
     */
    public function getData()
    {
        $args = [
            'from'      => isset($_GET['from']) ? sanitize_text_field($_GET['from']) : date('Y-m-d', strtotime('-1 month')),
            'to'        => isset($_GET['to']) ? sanitize_text_field($_GET['to']) : date('Y-m-d'),
            'post_type' => isset($_GET['pt']) ? sanitize_text_field($_GET['pt']) : Helper::get_list_post_type(),
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged()
        ];

        if (isset($_GET['order_by'])) {
            $args['order_by'] = sanitize_text_field($_GET['order_by']);
        } 
        
        if (isset($_GET['order'])) {
            $args['order'] = sanitize_text_field($_GET['order']);
        }

        $authorAnalyticsData  = new AuthorAnalyticsData($args);
        return $authorAnalyticsData->authorsReportData();
    }

    public function view()
    {
        $args = [
            'title'         => esc_html__('Authors', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug('author-analytics'),
            'pagination'    => Admin_Template::getCurrentPaged(),
            'DateRang'      => Admin_Template::DateRange(),
            'HasDateRang'   => true,
            'filters'       => ['post-type'],
            'backUrl'       => Menus::admin_url('author-analytics'),
            'backTitle'     => esc_html__('Authors Performance', 'wp-statistics'),
            'data'          => $this->getData()
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/authors-report', 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}