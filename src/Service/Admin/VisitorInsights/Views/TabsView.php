<?php

namespace WP_Statistics\Service\Admin\VisitorInsights\Views;

use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\UserOnline;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsDataProvider;

class TabsView extends BaseTabView
{
    private $isTrackLoggedInUsersEnabled;
    private $isOnlineUsersEnabled;

    protected $defaultTab = 'overview';
    protected $tabs = [
        'overview',
        'visitors',
        'views',
        'search-terms',
        'top-visitors'
    ];

    public function __construct()
    {
        $this->isTrackLoggedInUsersEnabled  = Option::get('visitors_log') ? true : false;
        $this->isOnlineUsersEnabled         = UserOnline::active();


        if ($this->isTrackLoggedInUsersEnabled) {
            $this->tabs[] = 'logged-in-users';
        }

        if ($this->isOnlineUsersEnabled) {
            $this->tabs[] = 'online';
        }

        $this->dataProvider = new VisitorInsightsDataProvider([
            'country'           => Request::get('location', ''),
            'agent'             => Request::get('agent', ''),
            'platform'          => Request::get('platform', ''),
            'user_id'           => Request::get('user_id', ''),
            'ip'                => Request::get('ip', ''),
            'referrer'          => Request::get('referrer', ''),
            'source_channel'    => Request::get('source_channel', ''),
        ]);

        parent::__construct();
    }

    public function getOverviewData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Visitors_Object', $this->dataProvider->getOverviewChartsData());

        return $this->dataProvider->getOverviewData();
    }

    public function getViewsData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Visitors_Object', $this->dataProvider->getViewsChartsData());

        return $this->dataProvider->getViewsData();
    }

    public function getVisitorsData()
    {
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

    public function getLoggedInUsersData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Visitors_Object', $this->dataProvider->getLoggedInChartsData());

        return $this->dataProvider->getLoggedInUsersData();
    }

    public function render()
    {
        $currentTab = $this->getCurrentTab();
        $data       = $this->getTabData();
        $filters    = $this->getActiveFilters();

        $args       = [
            'title'      => esc_html__('Visitor Insights', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('visitors'),
            'custom_get' => array_merge(['tab' => $currentTab], $filters),
            'DateRang'   => Admin_Template::DateRange(),
            'data'       => $data,
            'hasDateRang'=> true,
            'pagination' => Admin_Template::paginate_links([
                'total' => isset($data['total']) ? $data['total'] : 0,
                'echo'  => false
            ]),
            'tabs'       => [
                [
                    'link'  => Menus::admin_url('visitors', ['tab' => 'overview']),
                    'title' => esc_html__('Overview', 'wp-statistics'),
                    'class' => $this->isTab('overview') ? 'current' : '',
                ],
                [
                    'link'  => Menus::admin_url('visitors', ['tab' => 'visitors']),
                    'title' => esc_html__('Visitors', 'wp-statistics'),
                    'class' => $this->isTab('visitors') ? 'current' : '',
                ],
                [
                    'link'  => Menus::admin_url('visitors', ['tab' => 'views']),
                    'title' => esc_html__('Views', 'wp-statistics'),
                    'class' => $this->isTab('views') ? 'current' : '',
                ],
                [
                    'link'   => Menus::admin_url('visitors', ['tab' => 'online']),
                    'title'  => esc_html__('Online Visitors', 'wp-statistics'),
                    'class'  => $this->isTab('online') ? 'current wps-tab-link__online-visitors' : 'wps-tab-link__online-visitors',
                    'hidden' => !$this->isOnlineUsersEnabled
                ],
                [
                    'link'  => Menus::admin_url('visitors', ['tab' => 'top-visitors']),
                    'title' => esc_html__('Top Visitors', 'wp-statistics'),
                    'class' => $this->isTab('top-visitors') ? 'current' : ''
                ],
                [
                    'link'    => Menus::admin_url('visitors', ['tab' => 'logged-in-users']),
                    'title'   => esc_html__('Logged-in Users', 'wp-statistics'),
                    'tooltip' => esc_html__('Track engagement from logged-in users.', 'wp-statistics'),
                    'class'   => $this->isTab('logged-in-users') ? 'current' : '',
                    'hidden'  => !$this->isTrackLoggedInUsersEnabled
                ],
                [
                    'id'        => 'search_terms',
                    'link'      => Menus::admin_url('visitors', ['tab' => 'search-terms']),
                    'title'     => esc_html__('Search Terms', 'wp-statistics'),
                    'class'     => $this->isTab('search-terms') ? 'current' : '',
                    'tooltip'   => esc_html__('To view this report, you need to have the Data Plus add-on.', 'wp-statistics'),
                    'locked'    => !Helper::isAddOnActive('data-plus')
                ]
            ]
        ];

        // If Data Plus is active, relocate array items
        if (Helper::isAddOnActive('data-plus')) {
            $tabs = $args['tabs'];

            $searchTerms = null;

            foreach ($tabs as $key => $tab) {
                if (!isset($tab['id'])) continue;

                if ($tab['id'] === 'search_terms') {
                    $searchTerms = $key;
                }
            }

            $tabs = Helper::relocateArrayItems($tabs, $searchTerms, 3);

            $args['tabs'] = $tabs;
        }

        if ($this->isOnlineUsersEnabled && $this->isTab('online')) {
            $args['hasDateRang']        = false;
            $args['real_time_button']   = true;
        }

        if ($this->isTab('visitors')) {
            $args['filters'] = ['visitors'];
        }

        if ($this->isTab('logged-in-users')) {
            $args['filters'] = ['user-role'];
        }

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
        View::load("pages/visitor-insights/$currentTab", $args);
        do_action("wp_statistics_{$this->getCurrentPage()}_{$this->getCurrentTab()}_template", $args);
        Admin_Template::get_template(['layout/postbox.hide', 'layout/footer'], $args);
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

        if (Request::has('source_channel')) {
            $filters['source_channel'] = Request::get('source_channel');
        }

        if (Request::has('user_id')) {
            $filters['user_id'] = Request::get('user_id');
        }

        if (Request::has('ip')) {
            $filters['ip'] = Request::get('ip');
        }

        return $filters;
    }
}