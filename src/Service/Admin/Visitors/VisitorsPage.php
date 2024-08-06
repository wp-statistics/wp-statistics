<?php

namespace WP_Statistics\Service\Admin\Visitors;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Service\Admin\Visitors\Views\TabsView;

class VisitorsPage extends MultiViewPage
{
    protected $pageSlug = 'visitors-report';
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
