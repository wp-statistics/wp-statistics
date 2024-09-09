<?php

namespace WP_Statistics\Service\Admin\LicenseManager;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Service\Admin\LicenseManager\Views\TabsView;

class LicenseManagerPage extends MultiViewPage
{
    protected $pageSlug    = 'license_manager';
    protected $defaultView = 'tabs';
    protected $views       = ['tabs' => TabsView::class];

    public function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $this->disableScreenOption();
    }
}
