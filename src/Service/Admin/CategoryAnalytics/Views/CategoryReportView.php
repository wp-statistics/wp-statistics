<?php

namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsDataProvider;
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;
use WP_Statistics\Utils\Request;

class CategoryReportView extends BaseView
{

    public function __construct()
    {
        // Only allow the sortable columns of the report
        $orderBy = Request::get('order_by', 'views');
        if (!in_array($orderBy, ['term_name', 'views', 'posts', 'words', 'avg_views', 'avg_words'], true)) {
            $orderBy = 'views';
        }

        $args = [
            'author_id' => Request::get('author_id', '', 'number'),
            'post_type' => Request::get('pt', ''),
            'taxonomy'  => Request::get('tx', ''),
            'order_by'  => $orderBy,
            'order'     => Request::get('order', 'DESC'),
            'page'      => Admin_Template::getCurrentPaged(),
            'per_page'  => Admin_Template::$item_per_page,
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
            'page_title' => __('See how each category and tag performs', 'wp-statistics'),
            'benefits'   => [
                __('Reports for custom taxonomies', 'wp-statistics'),
                __('Views and top posts for each term', 'wp-statistics'),
                __('Link clicks and file downloads', 'wp-statistics'),
            ],
            'addon_name'         => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'         => 'wp-statistics-data-plus',
            'campaign'           => 'data-plus',
            'images'             => ['data-plus-advanced-filtering.png', 'data-plus-category.png', 'data-plus-comparison-widget.png', 'data-plus-download-tracker-recents.png'],
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }

    public function renderContent()
    {
        $data       = $this->getData();
        $postType   = Request::get('pt', 'post');
        $authorId   = Request::get('author_id', '', 'number');
        $parentPage = Menus::getCurrentPage();
        $args       = [
            'title'               => esc_html__('Category Report', 'wp-statistics'),
            'tooltip'             => esc_html__('Each term in this taxonomy, with stats for its content.', 'wp-statistics'),
            'pageName'            => Menus::get_page_slug($parentPage['page_url']),
            'custom_get'          => [
                'type'      => 'report',
                'pt'        => $postType,
                'author_id' => $authorId,
                'tx'        => Request::get('tx', 'category'),
                'order_by'  => Request::get('order_by', 'views'),
                'order'     => Request::get('order', 'desc')
            ],
            'DateRang'            => Admin_Template::DateRange(),
            'hasDateRang'         => true,
            'backUrl'             => Menus::admin_url($parentPage['page_url']),
            'backTitle'           => $parentPage['title'],
            'filters'             => ['post-types', 'author', 'taxonomy'],
            'lockCustomPostTypes' => true,
            'export'              => [ExportTypes::CSV_TABLE, ExportTypes::PDF_PAGE],
            'paged'               => Admin_Template::getCurrentPaged(),
            'data'                => $data
        ];

        if (isset($data['total']) && $data['total'] > 0) {
            $args['total'] = $data['total'];

            $args['pagination'] = Admin_Template::paginate_links([
                'total' => $data['total'],
                'echo'  => false
            ]);
        }

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