<?php

namespace WP_Statistics\Service\Admin\PageInsights;

use WP_Statistics\Abstracts\MultiViewPage;
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
    }

    protected function init()
    {
        $this->disableScreenOption();
    }
}
