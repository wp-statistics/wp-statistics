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

    public function render()
    {
        try {
            $currentTab = $this->getCurrentTab();
            $data       = $this->getTabData();

            $args = [
                'title'      => esc_html__('Visitor Insights', 'wp-statistics'),
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
                $args['filter'] = self::filter();
            }

            if (!$this->isTab('online') && !$this->isTab('top-visitors')) {
                $args['hasDateRang'] = true;
            }

            if ($this->isTab('online')){
                $args['real_time_button'] = true;
            }

            if ($this->isTab('top-visitors')){
                $args['datepicker'] = true;
                // Get Day
                $args['day'] = (isset($_GET['day']) ? sanitize_text_field($_GET['day']) : TimeZone::getCurrentDate('Y-m-d'));
            }

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
            View::load("pages/visitor-insights/$currentTab", $args);
            Admin_Template::get_template(['layout/postbox.hide', 'layout/visitors.filter', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }

    // @todo refactor visitor page filter
    public static function filter()
    {
        $params = 0;
        foreach ($_GET as $params_key => $params_item) {
            if (!in_array($params_key, ['pagination-page', 'page', 'order', 'orderby' , 'tab' ,'to' , 'from'])) {
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