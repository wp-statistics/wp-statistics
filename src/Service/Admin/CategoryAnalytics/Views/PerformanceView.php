<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use Exception;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Admin_Assets;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsDataProvider;

class PerformanceView extends BaseView 
{
    public function __construct()
    {
        $this->dataProvider = new CategoryAnalyticsDataProvider([
            'taxonomy'  => Request::get('tx', 'category'),
            'date'      => [
                'from'  => Request::get('from', date('Y-m-d', strtotime('-30 days'))),
                'to'    => Request::get('to', date('Y-m-d'))
            ],
        ]);
    }

    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus') && Helper::isCustomTaxonomy(Request::get('tx', 'category'));
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
            $template   = $this->isLocked() ? 'category-performance-locked' : 'category-performance';
    
            $args = [
                'title'         => esc_html__('Category Analytics', 'wp-statistics'),
                'pageName'      => Menus::get_page_slug('category-analytics'),
                'custom_get'    => ['tx' => Request::get('tx', 'category')],
                'DateRang'      => Admin_Template::DateRange(),
                'filters'       => ['taxonomy'],
                'hasDateRang'   => true,
                'data'          => $data
            ];

            Admin_Template::get_template(['layout/header', 'layout/title', "pages/category-analytics/$template", 'layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}