<?php

namespace WP_Statistics\Service\Admin\HelpCenter;

use WP_Statistics\Abstracts\MultiViewPage;

/**
 * Help Center Page.
 *
 * Renders the legacy Help Center page using PHP templates.
 *
 * @since 15.0.0
 */
class HelpCenterPage extends MultiViewPage
{
    protected $pageSlug = 'help';
    protected $defaultView = 'help-center';
    protected $views = [];

    public function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $this->disableScreenOption();
    }

    /**
     * Render the page.
     *
     * @return void
     */
    public function view()
    {
        \WP_STATISTICS\Admin_Template::get_template('pages/help-center');
    }
}
