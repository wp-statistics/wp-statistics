<?php 
namespace WP_Statistics\Service\AuthorAnalytics\Views;

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;

class SingleAuthorView
{
    public function view()
    {
        $args = [
            'title'      => esc_html__('Detailed Author Stats Locked: DataPlus Add-On Required', 'wp-statistics'),
            'backUrl'       => Menus::admin_url('author-analytics'),
            'backTitle'     => esc_html__('Authors Performance', 'wp-statistics'),
            ];
        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/author-single-locked', 'layout/footer'], $args);
    }
}