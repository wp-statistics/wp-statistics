<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics\Views;

use Exception;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class TabsView extends BaseTabView 
{
    protected $dataProvider;
    protected $defaultTab = 'performance';
    protected $tabs = [
        'performance',
        'pages'
    ];

    public function render()
    {
        try {
            $currentTab = $this->getCurrentTab();
    
            $args = [
                'title'         => esc_html__('Category Analytics', 'wp-statistics'),
                'tooltip'       => esc_html__('Category Analytics Tooltip', 'wp-statistics'),
                'pageName'      => Menus::get_page_slug('content-analytics'),
                'pagination'    => Admin_Template::getCurrentPaged(),
                'DateRang'      => Admin_Template::DateRange(),
                'filters'     => ['taxonomy'],
                'hasDateRang'   => true,
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
    
            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/category-analytics/category-$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}