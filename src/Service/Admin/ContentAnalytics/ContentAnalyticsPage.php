<?php

namespace WP_Statistics\Service\Admin\ContentAnalytics;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Service\Admin\ContentAnalytics\Views\SingleResourceView;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Service\Admin\FilterHandler\FilterGenerator;
use WP_Statistics\Service\Admin\ContentAnalytics\Views\TabsView;
use WP_Statistics\Service\Admin\ContentAnalytics\Views\SingleView;

class ContentAnalyticsPage extends MultiViewPage
{

    protected $pageSlug = 'content-analytics';
    protected $defaultView = 'tabs';
    protected $views = [
        'tabs'              => TabsView::class,
        'single'            => SingleView::class,
        'single-resource'   => SingleResourceView::class
    ];
    private $wordsCount;

    public function __construct()
    {
        parent::__construct();

        $this->setFilters();
    }

    protected function setFilters() {
        $this->filters = FilterGenerator::create()
            ->dropdown('qp', [
                'label' => esc_html__('Query Parameter', 'wp-statistics'),
                'panel' => true,
                'searchable' => true,
                'attributes'  => [
                    'data-type' => 'query-params',
                    'data-source' => 'getQueryParameters',
                ],
            ])
            ->get();

        return $this->filters;
    }

    protected function init()
    {
        $this->wordsCount = new WordCountService();

        $this->disableScreenOption();
    }
}
