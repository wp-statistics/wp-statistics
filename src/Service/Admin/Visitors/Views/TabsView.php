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
                'from' => Request::get('from', date('Y-m-d', strtotime('-29 days'))),
                'to'   => Request::get('to', date('Y-m-d'))
            ]
        ]);
    }

    public function getVisitorsData()
    {
        return $this->dataProvider->getVisitorsData();
    }

    public function render()
    {
        try {
            $currentTab = $this->getCurrentTab();
            $data       = $this->getTabData();

            $args = [
                'title'      => esc_html__('Visitors', 'wp-statistics'),
                'pageName'   => Menus::get_page_slug('visitors'),
                'custom_get' => ['tab' => $currentTab],
                'DateRang'   => Admin_Template::DateRange(),
                'data'       => $data,
                'pagination' => Admin_Template::paginate_links([
                    'total' => isset($data['total']) ? $data['total'] : 0,
                    'echo'  => false
                ]),
                'tabs'       => [
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
                        'link'  => Menus::admin_url('visitors-report', ['tab' => 'online']),
                        'title' => esc_html__('Online Visitors', 'wp-statistics'),
                        'class' => $currentTab === 'online' ? 'current wps-tab-link__online-visitors' : 'wps-tab-link__online-visitors',
                    ],
                    [
                        'link'    => Menus::admin_url('visitors-report', ['tab' => 'top-visitors']),
                        'title'   => esc_html__('Top Visitors', 'wp-statistics'),
                        'tooltip' => esc_html__('Top visitors tooltip', 'wp-statistics'),
                        'class'   => $currentTab === 'top-visitors' ? 'current' : '',
                    ]
                ]
            ];

            if ($currentTab === 'visitors') {
                $args['filter'] = self::filter();
            }
            if ($currentTab === 'online') {
                $args['real_time_button'] = true;
            }
            if ($currentTab !== 'visitors' && $currentTab !== 'online') {
                $args['hasDateRang'] = true;
            }

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
            View::load("pages/visitors/$currentTab", $args);
            Admin_Template::get_template(['layout/postbox.hide', 'layout/visitors.filter', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }

    public static function filter()
    {
        $params = 0;
        foreach ($_GET as $params_key => $params_item) {
            if (!in_array($params_key, ['page', 'from', 'to', 'order', 'orderby'])) {
                $params++;
            }
        }
        $filter['number'] = $params;
        // Determine classes based on conditions
        $activeClass = $filter['number'] > 0 ? 'wp-visitors-filter--active' : '';
        $floatClass  = is_rtl() ? 'wps-pull-left' : 'wps-pull-right';
        $badgeHTML   = '';
        if ($filter['number'] > 0) {
            $badgeHTML = '<span class="wps-badge">' . number_format_i18n($filter['number']) . '</span>';
        }

        // Code Button
        $filter['code'] = '
            <div class="' . $activeClass . ' ' . $floatClass . '" id="visitors-filter">
                <span class="dashicons dashicons-filter"></span>
                <span class="wps-visitor-filter__text">
                    <span class="filter-text">' . __("Filters", "wp-statistics") . '</span> 
                    ' . $badgeHTML . '
                </span>
                
            </div>
        ';
        return $filter;
    }
}