<?php 
namespace WP_Statistics\Service\AuthorAnalytics\Views;

use WP_STATISTICS\Admin_Template;

class SingleAuthorView
{
    public function view()
    {
        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/author-single-locked', 'layout/footer']);
    }
}