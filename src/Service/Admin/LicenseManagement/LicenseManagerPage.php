<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Service\Admin\LicenseManagement\Views\TabsView;
use WP_Statistics\Service\Admin\LicenseManagement\Views\LockedDataPlusView;

class LicenseManagerPage extends MultiViewPage
{
    protected $pageSlug    = 'plugins';
    protected $defaultView = 'tabs';
    protected $views       = [
        'tabs'              => TabsView::class,
        'locked-data-plus'  => LockedDataPlusView::class
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
