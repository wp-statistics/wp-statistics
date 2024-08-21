<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use Exception;
use WP_Statistics\Abstracts\BaseTabView;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Admin_Assets;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsDataProvider;

class TabsView extends BaseTabView 
{
    protected $defaultTab = 'category';

    public function __construct()
    {
        $this->dataProvider = new CategoryAnalyticsDataProvider([
            'taxonomy'  => Request::get('tab', 'category')
        ]);

        $this->tabs = array_keys(Helper::get_list_taxonomy(true));
    }

    public function isLockedTab($tab)
    {
        return !Helper::isAddOnActive('data-plus') && Helper::isCustomTaxonomy($tab);
    }

    public function getTabs()
    {
        $tabs = [];

        foreach ($this->tabs as $taxonomy) {
            $tab = [
                'link'    => Menus::admin_url('category-analytics', ['tab' => $taxonomy]),
                'title'   => ucwords(Helper::getTaxonomyName($taxonomy)),
                'class'   => $this->isTab($taxonomy) ? 'current' : ''
            ];

            if ($this->isLockedTab($taxonomy)) {
                $tab['locked']  = true;
                $tab['tooltip'] = esc_html__('To view reports for all your custom taxonomies, you need to have the Data Plus add-on.', 'wp-statistics');
            }

            $tabs[] = $tab;
        }

        return $tabs;
    }

    protected function getData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Category_Analytics_Object', $this->dataProvider->getChartsData());

        return $this->dataProvider->getPerformanceData();
    }

    public function render()
    {
        try {
            $data       = $this->getData();
            $template   = $this->isLockedTab($this->getCurrentTab()) ? 'category-performance-locked' : 'category-performance';
    
            $args = [
                'title'         => esc_html__('Category Analytics', 'wp-statistics'),
                'pageName'      => Menus::get_page_slug('category-analytics'),
                'custom_get'    => ['tab' => Request::get('tab', 'category')],
                'DateRang'      => Admin_Template::DateRange(),
                'hasDateRang'   => true,
                'data'          => $data,
                'tabs'          => $this->getTabs()
            ];

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/category-analytics/$template", 'layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}