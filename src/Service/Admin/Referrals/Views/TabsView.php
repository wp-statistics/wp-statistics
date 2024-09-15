<?php

namespace WP_Statistics\Service\Admin\Referrals\Views;

use Exception;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
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
            'order'    => Request::get('order', 'DESC'),
            'per_page' => Admin_Template::$item_per_page,
            'page'     => Admin_Template::getCurrentPaged(),
        ];

        $this->dataProvider = new ReferralsDataProvider($args);
    }

    public function getReferredVisitorsData()
    {
        return $this->dataProvider->getReferredVisitors();
    }

    public function render()
    {
        try {
            $data     = $this->getTabData();
            $template = $this->getCurrentTab();

            $args = [
                'title'       => esc_html__('Referrals', 'wp-statistics'),
                'tooltip'     => esc_html__('Referrals tooltip', 'wp-statistics'),
                'pageName'    => Menus::get_page_slug('referrals'),
                'custom_get'  => [
                    'tab'      => $this->getCurrentTab(),
                    'order_by' => Request::get('order_by'),
                    'order'    => Request::get('order'),
                ],
                'DateRang'    => Admin_Template::DateRange(),
                'filters'     => ['source-channel'],
                'hasDateRang' => true,
                'data'        => $data,
                'pagination'  => Admin_Template::paginate_links([
                    'total' => isset($data['total']) ? $data['total'] : 0,
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

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
            View::load("pages/referrals/$template", $args);
            Admin_Template::get_template(['layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}