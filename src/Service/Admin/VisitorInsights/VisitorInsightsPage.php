<?php

namespace WP_Statistics\Service\Admin\VisitorInsights;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\FilterHandler\FilterGenerator;
use WP_Statistics\Service\Admin\VisitorInsights\Views\TabsView;
use WP_Statistics\Service\Admin\VisitorInsights\Views\SingleVisitorView;

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
        $this->filters = FilterGenerator::create()
            ->hidden('pageName', [
                'name'  => 'page',
                'attributes' => [
                    'value' => Menus::get_page_slug('visitors')
                ]
            ])
            ->select('browsers', [
                'name' => 'agent',
            ])
            ->select('location', [
                'label' => esc_html__('Country', 'wp-statistics'),
            ])
            ->select('platform')
            ->select('referrer')
            ->select('users', [
                'name' => 'user_id',
                'label' => esc_html__('User', 'wp-statistics'),
            ])
            ->input('text', 'ip', [
                'label' => esc_html__('IP Address', 'wp-statistics'),
                'placeholder' => esc_attr__('Enter IP (e.g., 192.168.1.1) or hash (#...)', 'wp-statistics'),
            ])
            ->button('resetButton', [
                'type' => 'button',
                'classes' => 'wps-reset-filter wps-modal-reset-filter',
                'label' => esc_html__('Reset', 'wp-statistics'),
            ])
            ->button('submitButton', [
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
