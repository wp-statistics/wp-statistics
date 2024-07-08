<?php 
namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use InvalidArgumentException;
use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\Posts\PostsDataProvider;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;

class CategoryReportView extends BaseView
{

    public function __construct() 
    {

    }

    public function render()
    {
        $postType   = Request::get('pt', 'post');
        $authorId   = Request::get('author_id', '', 'number');
        $parentPage = Menus::getCurrentPage();

        $args = [
            'title'         => esc_html__('Category Report', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug($parentPage['page_url']),
            'custom_get'    => ['type' => 'posts', 'pt' => $postType, 'author_id' => $authorId],
            'DateRang'      => Admin_Template::DateRange(),
            'hasDateRang'   => true,
            'backUrl'       => Menus::admin_url($parentPage['page_url']),
            'backTitle'     => $parentPage['title'],
            'filters'       => ['post-type','author', 'taxonomy'],
            'paged'         => Admin_Template::getCurrentPaged()
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/category-analytics/category-report', 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}