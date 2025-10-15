<?php

namespace WP_Statistics\Service\Admin\Geographic;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Geographic\Views\SingleCountryView;
use WP_Statistics\Service\Admin\Geographic\Views\TabsView;

class GeographicPage extends MultiViewPage
{
    protected $pageSlug = 'geographic';
    protected $defaultView = 'tabs';
    protected $views = [
        'tabs'           => TabsView::class,
        'single-country' => SingleCountryView::class
    ];

    /**
     * @var VisitorsModel
     */
    private $visitorModel;

    public function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $this->visitorModel = new VisitorsModel();

        $this->disableScreenOption();
    }
}
