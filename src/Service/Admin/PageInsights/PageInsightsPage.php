<?php

namespace WP_Statistics\Service\Admin\PageInsights;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\FilterHandler\FilterGenerator;
use WP_Statistics\Service\Admin\PageInsights\Views\TabsView;
use WP_Statistics\Utils\Request;

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
        $authorId          = Request::get('author_id', '');
        $authorInfo        = get_userdata($authorId);
        $authorPlaceholder = ! empty($authorInfo) ? $authorInfo->display_name . ' #' . $authorInfo->ID : esc_attr__('All', 'wp-statistics');

        $url            = Request::get('url', '');
        $urlPlaceholder = ! empty($url) ? $url : esc_attr__('All', 'wp-statistics');

        $this->filters = FilterGenerator::create()
            ->hidden('pageName', [
                'name' => 'page',
                'attributes' => [
                    'value' => Menus::get_page_slug('pages')
                ]
            ])
            ->hidden('tab', [
                'name' => 'tab',
                'attributes' => [
                    'value' => Request::get('tab')
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
            ->select('userWithposts', [
                'name' => 'author_id',
                'placeholder' => $authorPlaceholder,
                'label' => esc_html__('Author', 'wp-statistics'),
                'attributes'  => [
                    'data-type'       => 'userWithposts',
                    'data-source'     => 'getUserWithPosts',
                    'data-searchable' => true,
                    'data-default'    => $authorId,
                ],
            ])
            ->select('url', [
                'label' => esc_html__('URL', 'wp-statistics'),
                'placeholder' => $urlPlaceholder,
                'attributes'  => [
                    'data-type'       => 'url',
                    'data-searchable' => true,
                    'data-default'    => $url,
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
