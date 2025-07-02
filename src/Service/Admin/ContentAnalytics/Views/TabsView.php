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
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Utils\Request;

class TabsView extends BaseTabView
{
    protected $defaultTab = 'post';

    public function __construct()
    {
        $this->dataProvider = new ContentAnalyticsDataProvider([
            'post_type' => Request::get('tab', 'post')
        ]);

        $this->tabs = Helper::getPostTypes();
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
                'link'    => Menus::admin_url('content-analytics', ['tab' => $postType]),
                'title'   => Helper::getPostTypeName($postType),
                'class'   => $this->isTab($postType) ? 'current' : ''
            ];

            if ($this->isLockedTab($postType)) {
                $tab['locked']  = true;
                $tab['tooltip'] = esc_html__('To view reports for all your custom post types, you need to have the Data Plus add-on.', 'wp-statistics');
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
            'page_title'        => esc_html__('Data Plus: Advanced Analytics for Deeper Insights', 'wp-statistics'),
            'page_second_title' => esc_html__('WP Statistics Premium: Beyond Just Data Plus', 'wp-statistics'),
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'content',
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

    public function render()
    {
        try {
            if ($this->isLockedTab($this->getCurrentTab())) {
                $this->renderLocked();
            } else {
                $this->renderContent();
            }

        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}