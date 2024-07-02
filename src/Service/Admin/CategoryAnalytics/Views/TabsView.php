<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use Exception;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsDataProvider;

class TabsView extends BaseTabView 
{
    protected $dataProvider;
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

    protected function getPagesData()
    {
        return $this->dataProvider->getPagesData();
    }

    public function render()
    {
        try {
            $currentTab = $this->getCurrentTab();
            $data       = $this->getTabData();
    
            $args = [
                'title'         => esc_html__('Category Analytics', 'wp-statistics'),
                'tooltip'       => esc_html__('Category Analytics Tooltip', 'wp-statistics'),
                'pageName'      => Menus::get_page_slug('category-analytics'),
                'custom_get'    => ['tab' => $currentTab],
                'DateRang'      => Admin_Template::DateRange(),
                'filters'       => ['taxonomy'],
                'hasDateRang'   => true,
                'data'          => $data,
                'tabs'          => [
                    [
                        'link'    => Menus::admin_url('category-analytics', ['tab' => 'performance']),
                        'title'   => esc_html__('Category Performance', 'wp-statistics'),
                        'tooltip' => esc_html__('Performance Tooltip.', 'wp-statistics'),
                        'class'   => $currentTab === 'performance' ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('category-analytics', ['tab' => 'pages']),
                        'title'   => esc_html__('Category Pages', 'wp-statistics'),
                        'tooltip' => esc_html__('Pages Tooltip.', 'wp-statistics'),
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
    
            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/category-analytics/category-$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}