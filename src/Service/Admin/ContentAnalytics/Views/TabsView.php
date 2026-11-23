<?php

namespace WP_Statistics\Service\Admin\ContentAnalytics\Views;

use Exception;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\ContentAnalytics\ContentAnalyticsDataProvider;
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Utils\Request;

class TabsView extends BaseTabView
{
    protected $defaultTab = 'post';

    public function __construct()
    {
        $this->tabs = Helper::getPostTypes();

        // Reject unknown post types before running any report query
        parent::__construct();

        $this->dataProvider = new ContentAnalyticsDataProvider([
            'post_type' => Request::get('tab', 'post'),
        ]);
    }

    /**
     * If DataPlus is not active and current tab is custom post type, it's locked
     */
    public function isLockedTab($tab)
    {
        return !Helper::isAddOnActive('data-plus') && in_array($tab, Helper::getCustomPostTypes());
    }

    public function getTabData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Content_Analytics_Object', $this->dataProvider->getChartsData());

        return $this->dataProvider->getPostTypeData();
    }

    public function getTabs()
    {
        $tabs = [];

        foreach (Helper::getPostTypes() as $postType) {
            $tab = [
                'id'      => $postType,
                'link'    => Menus::admin_url('content-analytics', ['tab' => $postType]),
                'title'   => Helper::getPostTypeName($postType),
                'class'   => $this->isTab($postType) ? 'current' : '',
                'export'  => [ExportTypes::CSV_METRICS, ExportTypes::PDF_PAGE]
            ];

            if ($this->isLockedTab($postType)) {
                $tab['locked']  = true;
                $tab['tooltip'] = esc_html__('Custom post type reports need the Data Plus add-on.', 'wp-statistics');
            }

            $tabs[] = $tab;
        }

        return $tabs;
    }

    public function renderContent()
    {
        $args = [
            'title'         => esc_html__('Content Analytics', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug('content-analytics'),
            'pagination'    => Admin_Template::getCurrentPaged(),
            'custom_get'    => ['tab' => $this->getCurrentTab()],
            'DateRang'      => Admin_Template::DateRange(),
            'hasDateRang'   => true,
            'tabs'          => $this->getTabs(),
            'data'          => $this->getTabData()
        ];

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/content-analytics/post-type", 'layout/postbox.hide', 'layout/footer'], $args);
    }

    public function renderLocked()
    {
        $args = [
            'page_title' => __('Deeper reports for every post and page', 'wp-statistics'),
            'benefits'   => [
                __('Custom post types and taxonomies', 'wp-statistics'),
                __('Link clicks and downloads per post', 'wp-statistics'),
                __('Weekly comparison and hourly traffic', 'wp-statistics'),
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

    public function render()
    {
        if ($this->isLockedTab($this->getCurrentTab())) {
            $this->renderLocked();
        } else {
            $this->renderContent();
        }
    }
}