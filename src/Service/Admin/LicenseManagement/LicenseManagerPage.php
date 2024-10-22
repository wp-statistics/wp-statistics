<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Views\TabsView;

class LicenseManagerPage extends MultiViewPage
{
    protected $pageSlug    = 'plugins';
    protected $defaultView = 'tabs';
    protected $views       = ['tabs' => TabsView::class];

    public function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $this->disableScreenOption();
        LicenseHelper::checkLicensesStatus();
    }
}
