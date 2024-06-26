<?php

namespace WP_Statistics\Service\Admin\CategoryAnalytics;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Service\Admin\CategoryAnalytics\Views\TabsView;
use WP_Statistics\Service\Admin\CategoryAnalytics\Views\SingleView;

class CategoryAnalyticsPage extends MultiViewPage
{

    protected $pageSlug = 'category-analytics';
    protected $defaultView = 'tabs';
    protected $views = [
        'tabs'      => TabsView::class,
        'single'    => SingleView::class
    ];

    public function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $this->disableScreenOption();
    }
}
