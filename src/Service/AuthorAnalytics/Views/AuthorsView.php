<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Views;

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;

class AuthorsView
{
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
            'backTitle'     => esc_html__('Authors Performance', 'wp-statistics')
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/authors-report', 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}