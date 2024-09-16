<?php

namespace WP_Statistics\Service\Admin\Referrals;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Service\Admin\Referrals\Views\TabsView;

class ReferralsPage extends MultiViewPage
{
    protected $pageSlug = 'referrals';
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
