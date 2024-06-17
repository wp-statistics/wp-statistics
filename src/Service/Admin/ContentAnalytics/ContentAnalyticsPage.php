<?php

namespace WP_Statistics\Service\Admin\ContentAnalytics;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Service\Admin\ContentAnalytics\Views\TabsView;
use WP_Statistics\Service\Admin\ContentAnalytics\Views\SingleView;

class ContentAnalyticsPage extends MultiViewPage
{

    protected $pageSlug = 'content-analytics';
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
