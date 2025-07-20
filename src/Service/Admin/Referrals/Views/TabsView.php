<?php

namespace WP_Statistics\Service\Admin\Referrals\Views;

use Exception;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Option;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Referrals\ReferralsDataProvider;

class TabsView extends BaseTabView
{
    protected $defaultTab = 'overview';
    protected $tabs = [
        'overview',
        'referred-visitors',
        'referrers',
        'search-engines',
        'campaigns',
        'utm-performance'
    ];

    public function __construct()
    {
        $args = [
            'referrer'       => Request::get('referrer'),
            'source_channel' => Request::get('source_channel'),
            'order'          => Request::get('order', 'DESC'),
            'per_page'       => Admin_Template::$item_per_page,
            'page'           => Admin_Template::getCurrentPaged()
        ];

        $this->dataProvider = new ReferralsDataProvider($args);
    }

    public function getOverviewData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Referrals_Object', $this->dataProvider->getReferralsOverviewChartData());

        return $this->dataProvider->getReferralsOverview();
    }

    public function getReferredVisitorsData()
    {
        return $this->dataProvider->getReferredVisitors();
    }

    public function getReferrersData()
    {
        return $this->dataProvider->getReferrers();
    }

    public function getSearchEnginesData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Referrals_Object', $this->dataProvider->getSearchEnginesChartsData());

        return $this->dataProvider->getSearchEngineReferrals();
    }

    public function getSourceCategoriesData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Referrals_Object', $this->dataProvider->getSourceCategoryChartsData());

        return $this->dataProvider->getSourceCategories();
    }

    public function getSocialMediaData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Referrals_Object', $this->dataProvider->getSocialMediaChartsData());

        return $this->dataProvider->getSocialMediaReferrals();
    }

    public function render()
    {
        try {
            $data     = $this->getTabData();
            $template = $this->getCurrentTab();

            $args = [
                'title'       => esc_html__('Referrals', 'wp-statistics'),
                'pageName'    => Menus::get_page_slug('referrals'),
                'custom_get'  => [
                    'tab'          => $this->getCurrentTab(),
                    'order_by'     => Request::get('order_by'),
                    'order'        => Request::get('order'),
                    'referrer'     => Request::get('referrer'),
                    'pid'          => Request::get('pid'),
                    'utm_source'   => Request::get('utm_source'),
                    'utm_medium'   => Request::get('utm_medium'),
                    'utm_campaign' => Request::get('utm_campaign'),
                    'utm_param'    => Request::get('utm_param')
                ],
                'filters'     => ['source-channels'],
                'DateRang'    => Admin_Template::DateRange(),
                'hasDateRang' => true,
                'data'        => $data,
                'pagination'  => Admin_Template::paginate_links([
                    'total' => $data['total'] ?? 0,
                    'echo'  => false
                ]),
                'tabs'        => [
                    [
                        'link'  => Menus::admin_url('referrals', ['tab' => 'overview']),
                        'title' => esc_html__('Overview', 'wp-statistics'),
                        'class' => $this->isTab('overview') ? 'current' : '',
                    ],
                    [
                        'link'  => Menus::admin_url('referrals', ['tab' => 'referred-visitors']),
                        'title' => esc_html__('Referred Visitors', 'wp-statistics'),
                        'class' => $this->isTab('referred-visitors') ? 'current' : '',
                    ],
                    [
                        'link'  => Menus::admin_url('referrals', ['tab' => 'referrers']),
                        'title' => esc_html__('Referrers', 'wp-statistics'),
                        'class' => $this->isTab('referrers') ? 'current' : '',
                    ],
                    [
                        'link'  => Menus::admin_url('referrals', ['tab' => 'search-engines']),
                        'title' => esc_html__('Search Engines', 'wp-statistics'),
                        'class' => $this->isTab('search-engines') ? 'current' : '',
                    ],
                    [
                        'link'  => Menus::admin_url('referrals', ['tab' => 'social-media']),
                        'title' => esc_html__('Social Media', 'wp-statistics'),
                        'class' => $this->isTab('social-media') ? 'current' : '',
                    ],
                    [
                        'link'  => Menus::admin_url('referrals', ['tab' => 'source-categories']),
                        'title' => esc_html__('Source Categories', 'wp-statistics'),
                        'class' => $this->isTab('source-categories') ? 'current' : '',
                    ],
                    [
                        'link'         => Menus::admin_url('referrals', ['tab' => 'campaigns']),
                        'title'        => esc_html__('Campaigns', 'wp-statistics'),
                        'class'        => $this->isTab('campaigns') ? 'current' : '',
                        'locked'       => true,
                        'tooltip'      => esc_html__('To view this report, you need to have Marketing add-on.', 'wp-statistics'),
                        'lockedTarget' => 'wp-statistics-marketing'
                    ],
                    [
                        'link'         => Menus::admin_url('referrals', ['tab' => 'utm-performance']),
                        'title'        => esc_html__('UTM Performance', 'wp-statistics'),
                        'class'        => $this->isTab('utm-performance') ? 'current' : '',
                        'locked'       => true,
                        'tooltip'      => esc_html__('To view this report, you need to have Marketing add-on.', 'wp-statistics'),
                        'lockedTarget' => 'wp-statistics-marketing'
                    ],
                    [
                        'link'               => Menus::admin_url('referrals', ['tab' => 'google-search']),
                        'title'              => esc_html__('Google Search', 'wp-statistics'),
                        'class'              => $this->isTab('google-search') ? 'current' : '',
                        'lastUpdated'        => true,
                        'lastUpdatedTooltip' => esc_html__('We fetch data from Google Search Console once daily to keep things running smoothly without extra load. The numbers you see are based on the latest update at the time shown.', 'wp-statistics'),
                        'locked'             => true,
                        'hidden'             => !Option::getByAddon('gsc_report', 'marketing', '1') && !Option::getByAddon('site', 'marketing'),
                        'tooltip'            => esc_html__('To view this report, you need to have Marketing add-on.', 'wp-statistics'),
                        'lockedTarget'       => 'wp-statistics-marketing'
                    ]
                ]
            ];

            // Remove filters in overview tab
            if ($this->isTab('overview')) {
                $args['filters'] = [];
            }

            // Add referrer filter if tab is referred visitors
            if ($this->isTab('referred-visitors')) {
                array_unshift($args['filters'], 'referrer');
            }

            // Add UTM filter if tab is UTM performance
            if ($this->isTab(['utm-performance'])) {
                array_unshift($args['filters'], 'utm');
            }

            // Add UTM filter if tab is campaigns
            if ($this->isTab(['campaigns'])) {
                array_unshift($args['filters'], 'campaigns');
            }

            // Remove source channels filter if tab is source categories or utm-performance or campaigns
            if ($this->isTab(['source-categories', 'utm-performance', 'campaigns', 'google-search', 'referrers', 'overview'])) {
                $args['filters'] = array_values(array_diff($args['filters'], ['source-channels']));
            }

            // Add search channels filter if tab is search engines
            if ($this->isTab('search-engines')) {
                $args['filters'] = ['search-channels'];
            }

            // Add social channels filter if tab is it's social media tab
            if ($this->isTab('social-media')) {
                $args['filters'] = ['social-channels'];
            }

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
            View::load("pages/referrals/$template", $args);
            do_action("wp_statistics_{$this->getCurrentPage()}_{$this->getCurrentTab()}_template", $args);
            Admin_Template::get_template(['layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}