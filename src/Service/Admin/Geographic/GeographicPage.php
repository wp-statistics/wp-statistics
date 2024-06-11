<?php

namespace WP_Statistics\Service\Admin\Geographic;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Service\Admin\Geographic\Views\SingleView;
use WP_Statistics\Service\Admin\Geographic\Views\TabsView;

class GeographicPage extends MultiViewPage
{
    protected $pageSlug = 'geographic';
    protected $defaultView = 'tabs';
    protected $views = [
        'tabs'   => TabsView::class,
        'single' => SingleView::class
    ];

    public function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {

    }
}
