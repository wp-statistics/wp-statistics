<?php

namespace WP_Statistics\Service\Admin\PageInsights;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\FilterHandler\FilterGenerator;
use WP_Statistics\Service\Admin\PageInsights\Views\TabsView;

class PageInsightsPage extends MultiViewPage
{
    protected $pageSlug = 'pages';
    protected $defaultView = 'tabs';
    protected $views = [
        'tabs' => TabsView::class
    ];

    public function __construct()
    {
        parent::__construct();

        $this->setFilters();
    }

    protected function setFilters() {
        $this->filters = FilterGenerator::create()
            ->hidden('pageName', [
                'name' => 'page',
                'attributes' => [
                    'value' => Menus::get_page_slug('pages')
                ]
            ])
            ->dropdown('tx', [
                'label' => esc_html__('Taxonomy', 'wp-statistics'),
                'selected' => 'category',
                'panel' => true,
                'attributes'  => [
                    'data-type' => 'taxonomy',
                    'data-source' => 'getTaxonomies',
                ],
            ])
            ->select('usersWithPosts', [
                'name' => 'author_id',
                'label' => esc_html__('Author', 'wp-statistics')
            ])
            ->select('url', [
                'classes' => 'wps-width-100 wps-select2',
                'attributes'  => [
                    'data-type'       => 'url',
                    'data-searchable' => true,
                ],
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
            ->dropdown('pt', [
                'label' => esc_html__('Post Type', 'wp-statistics'),
                'panel' => true,
                'attributes'  => [
                    'data-type' => 'post-types',
                    'data-source' => 'getPostTypes',
                ],
            ])
            ->get();
        
        return $this->filters;
    }

    protected function init()
    {
        $this->disableScreenOption();
    }
}
