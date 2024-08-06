<?php 

namespace WP_Statistics\Service\Admin\Visitors\Views;

use Exception;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Visitors\VisitorsDataProvider;

class TabsView extends BaseTabView 
{
    protected $defaultTab = 'visitors';
    protected $tabs = [
        'visitors',
        'views',
        'online',
        'top-visitors'
    ];

    public function __construct()
    {
        $this->dataProvider = new VisitorsDataProvider([
            'date' => [
                'from'  => Request::get('from', date('Y-m-d', strtotime('-29 days'))),
                'to'    => Request::get('to', date('Y-m-d'))
            ]
        ]);
    }

    public function render()
    {
        try {
            $currentTab = $this->getCurrentTab();
            $data       = $this->getTabData();
    
            $args = [
                'title'         => esc_html__('Visitors', 'wp-statistics'),
                'pageName'      => Menus::get_page_slug('visitors'),
                'custom_get'    => ['tab' => $currentTab],
                'DateRang'      => Admin_Template::DateRange(),
                'hasDateRang'   => true,
                'data'          => $data,
                'pagination'    => Admin_Template::paginate_links([
                    'total' => isset($data['total']) ? $data['total'] : 0,
                    'echo'  => false
                ]),
                'tabs'          => [
                    [
                        'link'    => Menus::admin_url('visitors-report', ['tab' => 'visitors']),
                        'title'   => esc_html__('Visitors', 'wp-statistics'),
                        'tooltip' => esc_html__('Visitors tooltip', 'wp-statistics'),
                        'class'   => $currentTab === 'visitors' ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('visitors-report', ['tab' => 'views']),
                        'title'   => esc_html__('Views', 'wp-statistics'),
                        'tooltip' => esc_html__('Views tooltip', 'wp-statistics'),
                        'class'   => $currentTab === 'views' ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('visitors-report', ['tab' => 'online']),
                        'title'   => esc_html__('Online', 'wp-statistics'),
                        'tooltip' => esc_html__('Online tooltip', 'wp-statistics'),
                        'class'   => $currentTab === 'online' ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('visitors-report', ['tab' => 'top-visitors']),
                        'title'   => esc_html__('Top Visitors', 'wp-statistics'),
                        'tooltip' => esc_html__('Top visitors tooltip', 'wp-statistics'),
                        'class'   => $currentTab === 'top-visitors' ? 'current' : '',
                    ]
                ]
            ];

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
            View::load("pages/visitors/$currentTab");
            Admin_Template::get_template(['layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}