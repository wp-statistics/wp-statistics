<?php

namespace WP_Statistics\Service\Admin\Referrals\Views;

use Exception;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Assets;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Referrals\ReferralsDataProvider;

class TabsView extends BaseTabView
{
    protected $defaultTab = 'referred-visitors';
    protected $tabs = [
        'referred-visitors',
        'referrers',
        'search-engines'
    ];

    public function __construct()
    {
        $args = [
            'referrer'          => Request::get('referrer'),
            'source_channel'    => Request::get('source_channel'),
            'order'             => Request::get('order', 'DESC'),
            'per_page'          => Admin_Template::$item_per_page,
            'page'              => Admin_Template::getCurrentPaged()
        ];

        $this->dataProvider = new ReferralsDataProvider($args);
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
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Referrals_Object', $this->dataProvider->getChartsData());

        return $this->dataProvider->getSearchEngineReferrals();
    }

    public function render()
    {
        try {
            $data       = $this->getTabData();
            $template   = $this->getCurrentTab();

            $args = [
                'title'       => esc_html__('Referrals', 'wp-statistics'),
                'pageName'    => Menus::get_page_slug('referrals'),
                'custom_get'  => [
                    'tab'       => $this->getCurrentTab(),
                    'order_by'  => Request::get('order_by'),
                    'order'     => Request::get('order'),
                    'referrer'  => Request::get('referrer')
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
                    ]
                ]
            ];

            // Add referrer filter if tab is referred visitors
            if ($this->isTab('referred-visitors')) {
                array_unshift($args['filters'], 'referrer');
            }

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
            View::load("pages/referrals/$template", $args);
            Admin_Template::get_template(['layout/postbox.hide', 'layout/referrer.filter', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}