<?php

namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use Exception;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Admin_Assets;
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsDataProvider;

class TabsView extends BaseTabView
{
    protected $defaultTab = 'category';

    public function __construct()
    {
        $this->tabs = array_keys(Helper::get_list_taxonomy(true));

        // Reject unknown taxonomies before running any report query
        parent::__construct();

        $this->dataProvider = new CategoryAnalyticsDataProvider([
            'taxonomy' => Request::get('tab', 'category')
        ]);
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
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'data-plus',
            'images'            => ['data-plus-advanced-filtering.png','data-plus-category.png','data-plus-comparison-widget.png','data-plus-download-tracker-recents.png'],
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }

    public function renderContent()
    {
        $args = [
            'title'       => esc_html__('Category Analytics', 'wp-statistics'),
            'pageName'    => Menus::get_page_slug('category-analytics'),
            'custom_get'  => ['tab' => Request::get('tab', 'category')],
            'DateRang'    => Admin_Template::DateRange(),
            'hasDateRang' => true,
            'data'        => $this->getData(),
            'tabs'        => $this->getTabs()
        ];

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/category-analytics/category-performance", 'layout/postbox.hide', 'layout/footer'], $args);
    }

    public function render()
    {
        if ($this->isLockedTab($this->getCurrentTab())) {
            $this->renderLocked();
        } else {
            $this->renderContent();
        }
    }

    protected function getData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Category_Analytics_Object', $this->dataProvider->getChartsData());

        return $this->dataProvider->getPerformanceData();
    }

    public function getTabs()
    {
        $tabs = [];

        foreach ($this->tabs as $taxonomy) {
            $tab = [
                'id'    => $taxonomy,
                'link'  => Menus::admin_url('category-analytics', ['tab' => $taxonomy]),
                'title' => ucwords(Helper::getTaxonomyName($taxonomy)),
                'class' => $this->isTab($taxonomy) ? 'current' : '',
                'export'=> [ExportTypes::CSV_METRICS, ExportTypes::PDF_PAGE]
            ];

            if ($this->isLockedTab($taxonomy)) {
                $tab['locked']  = true;
                $tab['tooltip'] = esc_html__('Custom taxonomy reports need the Data Plus add-on.', 'wp-statistics');
            }

            $tabs[] = $tab;
        }

        return $tabs;
    }

    public function isLockedTab($tab)
    {
        return !Helper::isAddOnActive('data-plus') && Helper::isCustomTaxonomy($tab);
    }
}