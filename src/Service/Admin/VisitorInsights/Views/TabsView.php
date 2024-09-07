<?php

namespace WP_Statistics\Service\Admin\VisitorInsights\Views;

use Exception;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsDataProvider;

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
        $this->dataProvider = new VisitorInsightsDataProvider([
            'country'   => Request::get('location', ''),
            'agent'     => Request::get('agent', ''),
            'platform'  => Request::get('platform', ''),
            'user_id'   => Request::get('user_id', ''),
            'ip'        => Request::get('ip', '')
        ]);
    }

    public function getVisitorsData()
    {
        return $this->dataProvider->getVisitorsData();
    }

    public function getViewsData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Visitors_Object', $this->dataProvider->getChartsData());

        return $this->dataProvider->getVisitorsData();
    }

    public function getOnlineData()
    {
        return $this->dataProvider->getOnlineVisitorsData();
    }

    public function getTopVisitorsData()
    {
        return $this->dataProvider->getTopVisitorsData();
    }

    public function getActiveFilters()
    {
        $filters = [];

        if (Request::has('agent')) {
            $filters['agent'] = Request::get('agent');
        }

        if (Request::has('location')) {
            $filters['location'] = Request::get('location');
        }

        if (Request::has('platform')) {
            $filters['platform'] = Request::get('platform');
        }

        if (Request::has('referrer')) {
            $filters['referrer'] = Request::get('referrer');
        }

        if (Request::has('user_id')) {
            $filters['user_id'] = Request::get('user_id');
        }

        if (Request::has('ip')) {
            $filters['ip'] = Request::get('ip');
        }

        return $filters;
    }

    public function render()
    {
        try {
            $currentTab = $this->getCurrentTab();
            $data       = $this->getTabData();
            $filters    = $this->getActiveFilters();

            $args = [
                'title'      => esc_html__('Visitor Insights', 'wp-statistics'),
                'pageName'   => Menus::get_page_slug('visitors'),
                'custom_get' => array_merge(['tab' => $currentTab], $filters),
                'DateRang'   => Admin_Template::DateRange(),
                'data'       => $data,
                'pagination' => Admin_Template::paginate_links([
                    'total' => isset($data['total']) ? $data['total'] : 0,
                    'echo'  => false
                ]),
                'tabs'       => [
                    [
                        'link'    => Menus::admin_url('visitors', ['tab' => 'visitors']),
                        'title'   => esc_html__('Visitors', 'wp-statistics'),
                        'class'   => $this->isTab('visitors') ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('visitors', ['tab' => 'views']),
                        'title'   => esc_html__('Views', 'wp-statistics'),
                        'class'   => $this->isTab('views') ? 'current' : '',
                    ],
                    [
                        'link'  => Menus::admin_url('visitors', ['tab' => 'online']),
                        'title' => esc_html__('Online Visitors', 'wp-statistics'),
                        'class' => $this->isTab('online') ? 'current wps-tab-link__online-visitors' : 'wps-tab-link__online-visitors',
                    ],
                    [
                        'link'    => Menus::admin_url('visitors', ['tab' => 'top-visitors']),
                        'title'   => esc_html__('Top Visitors', 'wp-statistics'),
                        'class'   => $this->isTab('top-visitors') ? 'current' : '',
                     ]
                ]
            ];

            if ($this->isTab('visitors')) {
                $args['filters'] = ['visitors'];
            }

            if (!$this->isTab('online') ) {
                $args['hasDateRang'] = true;
            }

            if ($this->isTab('online')){
                $args['real_time_button'] = true;
            }

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
            View::load("pages/visitor-insights/$currentTab", $args);
            Admin_Template::get_template(['layout/postbox.hide', 'layout/visitors.filter', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}