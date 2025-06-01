<?php
namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
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

    public function renderLocked()
    {
        $args = [
            'page_title'        => esc_html__('Data Plus: Advanced Analytics for Deeper Insights', 'wp-statistics'),
            'page_second_title' => esc_html__('WP Statistics Premium: Beyond Just Data Plus', 'wp-statistics'),
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'data-plus',
            'more_title'        => esc_html__('Learn More About Data Plus', 'wp-statistics'),
            'premium_btn_title' => esc_html__('Upgrade Now to Unlock All Premium Features!', 'wp-statistics'),
            'images'            => ['data-plus-advanced-filtering.png','data-plus-category.png','data-plus-comparison-widget.png','data-plus-download-tracker-recents.png'],
            'description'       => esc_html__('Data Plus is a premium add-on for WP Statistics that unlocks powerful analytics features, providing a complete view of your site’s performance. Take advantage of advanced tools that help you understand visitor behavior, enhance your content, and track engagement on a new level. With Data Plus, you can make data-driven decisions to grow your site more effectively.', 'wp-statistics'),
            'second_description'=> esc_html__('When you upgrade to WP Statistics Premium, you don’t just get Data Plus — you gain access to all premium add-ons, delivering detailed insights and tools for every aspect of your site.', 'wp-statistics')
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }

    public function renderContent()
    {
        $postType   = Request::get('pt', 'post');
        $authorId   = Request::get('author_id', '', 'number');
        $parentPage = Menus::getCurrentPage();
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

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/category-analytics/category-report", 'layout/postbox.toggle', 'layout/footer'], $args);
    }

    public function render()
    {
        if ($this->isLocked()) {
            $this->renderLocked();
        } else {
            $this->renderContent();
        }
    }
}