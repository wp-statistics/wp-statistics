<?php 
namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsDataProvider;
use WP_Statistics\Utils\Request;

class CategoryReportView extends BaseView
{

    public function __construct() 
    {
        $this->dataProvider = new CategoryAnalyticsDataProvider([
            'author_id' => Request::get('author_id', '', 'number'),
            'post_type' => Request::get('pt', ''),
            'taxonomy'  => Request::get('tx', 'category'),
            'date'      => [
                'from' => Request::get('from', date('Y-m-d', strtotime('-30 days'))),
                'to'   => Request::get('to', date('Y-m-d'))
            ]
        ]);
    }

    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus') && Helper::isCustomTaxonomy(Request::get('tx', 'category'));
    }

    public function getData()
    {
        return $this->dataProvider->getCategoryReportData();
    }

    public function render()
    {
        $postType   = Request::get('pt', 'post');
        $authorId   = Request::get('author_id', '', 'number');
        $parentPage = Menus::getCurrentPage();
        $template   = 'category-report';

        if ($this->isLocked()) {
            $template = 'category-pages-locked';
        }

        $args = [
            'title'         => esc_html__('Category Report', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug($parentPage['page_url']),
            'custom_get'    => ['type' => 'posts', 'pt' => $postType, 'author_id' => $authorId],
            'DateRang'      => Admin_Template::DateRange(),
            'hasDateRang'   => true,
            'backUrl'       => Menus::admin_url($parentPage['page_url']),
            'backTitle'     => $parentPage['title'],
            'filters'       => ['post-type','author', 'taxonomy'],
            'paged'         => Admin_Template::getCurrentPaged(),
            'data'          => $this->getData()
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/category-analytics/$template", 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}