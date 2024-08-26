<?php 

namespace WP_Statistics\Service\Admin\ContentAnalytics\Views;

use Exception;
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

    public function render()
    {
        try {
            $postType = $this->getCurrentTab();
            $template = 'post-type';

            if ($this->isLockedTab($postType)) {
                $template = 'post-type-locked';
            }
    
            $args = [
                'title'         => esc_html__('Content Analytics', 'wp-statistics'),
                'pageName'      => Menus::get_page_slug('content-analytics'),
                'pagination'    => Admin_Template::getCurrentPaged(),
                'custom_get'    => ['tab' => $postType],
                'DateRang'      => Admin_Template::DateRange(),
                'hasDateRang'   => true,
                'tabs'          => $this->getTabs(),
                'data'          => $this->getTabData()
            ];
    
            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/content-analytics/$template", 'layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}