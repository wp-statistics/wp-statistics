<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\AuthorAnalytics\AuthorAnalyticsDataProvider;
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;
use WP_Statistics\Utils\Request;

class PerformanceView extends BaseView
{
    public function __construct()
    {
        $args = [
            'post_type' => Request::get('pt', 'post')
        ];

        $this->dataProvider = new AuthorAnalyticsDataProvider($args);
    }

    public function getData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Author_Analytics_Object', $this->dataProvider->getAuthorsChartData());

        return $this->dataProvider->getAuthorsPerformanceData();
    }

    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus') && Helper::isCustomPostType(Request::get('pt', 'post'));
    }

    public function renderContent()
    {
        $args = [
            'title'       => esc_html__('Author Analytics', 'wp-statistics'),
            'pageName'    => Menus::get_page_slug('author-analytics'),
            'paged'       => Admin_Template::getCurrentPaged(),
            'custom_get'  => ['pt' => Request::get('pt', 'post')],
            'export'      => [ExportTypes::CSV_METRICS, ExportTypes::PDF_PAGE],
            'DateRang'    => Admin_Template::DateRange(),
            'hasDateRang' => true,
            'filters'     => ['post-type'],
            'data'        => $this->getData()
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/author-analytics/authors-performance", 'layout/postbox.hide', 'layout/footer'], $args);
    }

    public function renderLocked()
    {
        $args = [
            'page_title' => __('See how each author performs', 'wp-statistics'),
            'benefits'   => [
                __('Views and posts for every author', 'wp-statistics'),
                __('Each author\'s top posts', 'wp-statistics'),
                __('Engagement trends over time', 'wp-statistics'),
            ],
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'data-plus',
            'images'            => ['data-plus-single-author.png'],
        ];
        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
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