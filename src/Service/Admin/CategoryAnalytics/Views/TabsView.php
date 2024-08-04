<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use Exception;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Admin_Assets;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsDataProvider;

class TabsView extends BaseTabView 
{
    protected $defaultTab = 'performance';
    protected $tabs = [
        'performance',
        'pages'
    ];

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

    protected function getPerformanceData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Category_Analytics_Object', $this->dataProvider->getChartsData());

        return $this->dataProvider->getPerformanceData();
    }

    protected function getPagesData()
    {
        return $this->dataProvider->getPagesData();
    }

    public function render()
    {
        try {
            $currentTab = $this->getCurrentTab();
            $data       = $this->getTabData();
            $template   = "category-$currentTab";

            if ($this->isLocked()) {
                $template = "category-$currentTab-locked";
            }
    
            $args = [
                'title'         => esc_html__('Category Analytics', 'wp-statistics'),
                'pageName'      => Menus::get_page_slug('category-analytics'),
                'custom_get'    => ['tab' => $currentTab, 'tx' => Request::get('tx', 'category')],
                'DateRang'      => Admin_Template::DateRange(),
                'filters'       => ['taxonomy'],
                'hasDateRang'   => true,
                'data'          => $data,
                'tabs'          => [
                    [
                        'link'    => Menus::admin_url('category-analytics', ['tab' => 'performance']),
                        'title'   => esc_html__('Category Performance', 'wp-statistics'),
                        'tooltip' => esc_html__('Displays detailed performance metrics of content with the selected taxonomy.', 'wp-statistics'),
                        'class'   => $currentTab === 'performance' ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('category-analytics', ['tab' => 'pages']),
                        'title'   => esc_html__('Category Pages', 'wp-statistics'),
                        'tooltip' => esc_html__('Shows the page views for category pages related to the selected taxonomy.', 'wp-statistics'),
                        'class'   => $currentTab === 'pages' ? 'current' : '',
                    ]
                ]
            ];

            if ($currentTab === 'pages') {
                if ($data['total'] > 0) {
                    $args['total'] = $data['total'];

                    $args['pagination'] = Admin_Template::paginate_links([
                        'total' => $data['total'],
                        'echo'  => false
                    ]);
                }
            }
    
            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/category-analytics/$template", 'layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}