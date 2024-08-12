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
        $args = [
            'author_id' => Request::get('author_id', '', 'number'),
            'post_type' => Request::get('pt', ''),
            'taxonomy'  => Request::get('tx', 'category'),
            'order_by'  => Request::get('order_by', 'views'),
            'order'     => Request::get('order', 'DESC'),
        ];

        // If data plus is active, show all post-types, otherwise, just built-in post-types
        if (Helper::isAddOnActive('data-plus') && !Request::has('pt')) {
            $args['post_type'] = Helper::getPostTypes();
        } else if (!Helper::isAddOnActive('data-plus') && !Request::has('pt')) {
            $args['post_type'] = Helper::getDefaultPostTypes();
        }

        $this->dataProvider = new CategoryAnalyticsDataProvider($args);
    }

    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus') && (Helper::isCustomTaxonomy(Request::get('tx', 'category')) || Helper::isCustomPostType(Request::get('pt', 'post')));
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
            $template = 'category-report-locked';
        }

        $args = [
            'title'                 => esc_html__('Category Report', 'wp-statistics'),
            'tooltip'               => esc_html__('List of terms in the selected taxonomy with metrics for content associated with each term.', 'wp-statistics'),
            'pageName'              => Menus::get_page_slug($parentPage['page_url']),
            'custom_get'            => [
                'type'      => 'report', 
                'pt'        => $postType, 
                'author_id' => $authorId, 
                'tx'        => Request::get('tx', 'category'),
                'order_by'  => Request::get('order_by', 'views'),
                'order'     => Request::get('order', 'desc')
            ],
            'DateRang'              => Admin_Template::DateRange(),
            'hasDateRang'           => true,
            'backUrl'               => Menus::admin_url($parentPage['page_url']),
            'backTitle'             => $parentPage['title'],
            'filters'               => ['post-types','author', 'taxonomy'],
            'lockCustomPostTypes'   => true,
            'paged'                 => Admin_Template::getCurrentPaged(),
            'data'                  => $this->getData()
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/category-analytics/$template", 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}