<?php

namespace WP_Statistics\Service\Admin\VisitorInsights;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\FilterHandler\FilterGenerator;
use WP_Statistics\Service\Admin\VisitorInsights\Views\TabsView;
use WP_Statistics\Service\Admin\VisitorInsights\Views\SingleVisitorView;
use WP_Statistics\Utils\Request;

class VisitorInsightsPage extends MultiViewPage
{
    protected $pageSlug = 'visitors';
    protected $defaultView = 'tabs';
    protected $views = [
        'tabs'           => TabsView::class,
        'single-visitor' => SingleVisitorView::class
    ];

    public function __construct()
    {
        parent::__construct();

        $this->setFilters();
    }

    protected function init()
    {
        $this->disableScreenOption();
    }

    protected function setFilters() {
        $userId          = Request::get('user_id', '');
        $authorInfo      = get_userdata($userId);
        $userPlaceholder = ! empty($authorInfo) ? $authorInfo->display_name . ' #' . $authorInfo->ID : esc_html__('All', 'wp-statistics');

        $referrer            = Request::get('referrer', '');
        $referrerPlaceholder = ! empty($referrer) ? $referrer : esc_html__('All', 'wp-statistics');

        $this->filters = FilterGenerator::create()
            ->hidden('pageName', [
                'name'  => 'page',
                'attributes' => [
                    'value' => Menus::get_page_slug('visitors')
                ]
            ])
            ->select('browsers', [
                'label' => esc_html__('Browsers', 'wp-statistics'),
                'name' => 'agent',
            ])
            ->select('location', [
                'label' => esc_html__('Country', 'wp-statistics'),
            ])
            ->select('platform', [
                'label' => esc_html__('Platform', 'wp-statistics'),
            ])
            ->select('referrer', [
                'name' => 'referrer',
                'placeholder' => $referrerPlaceholder,
                'label' => esc_html__('Referrer', 'wp-statistics'),
                'attributes'  => [
                    'data-type'       => 'referrer',
                    'data-source'     => 'getReferrer',
                    'data-searchable' => true,
                    'data-default'    => $referrer,
                ],
            ])
            ->select('users', [
                'name' => 'user_id',
                'placeholder' => $userPlaceholder,
                'label' => esc_html__('User', 'wp-statistics'),
                'attributes'  => [
                    'data-type'       => 'users',
                    'data-source'     => 'getUser',
                    'data-searchable' => true,
                    'data-default'    => $userId,
                ],
            ])
            ->input('text', 'ip', [
                'label' => esc_html__('IP Address', 'wp-statistics'),
                'placeholder' => esc_attr__('Enter IP (e.g., 192.168.1.1) or hash (#...)', 'wp-statistics'),
            ])
            ->button('resetButton', [
                'name' => 'reset',
                'type' => 'button',
                'classes' => 'wps-reset-filter wps-modal-reset-filter',
                'label' => esc_html__('Reset', 'wp-statistics'),
            ])
            ->button('submitButton', [
                'name' => 'filter',
                'type' => 'button',
                'classes' => 'button-primary',
                'label' => esc_html__('Filter', 'wp-statistics'),
                'attributes'  => [
                    'type' => 'submit',
                ],
            ])
            ->dropdown('role', [
                'label' => esc_html__('User Role', 'wp-statistics'),
                'panel' => true,
                'searchable' => true,
                'attributes'  => [
                    'data-type' => 'user-role',
                    'data-source' => 'getUserRoles',
                ],
            ])
            ->get();
        
        return $this->filters;
    }
}
