<?php

namespace WP_Statistics\Service\Admin\HelpCenter;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Assets;

class HelpCenterPage extends BasePage
{
    protected $pageSlug = 'help-center';
    protected $dataProvider;

    public function __construct()
    {
        parent::__construct();

        $this->dataProvider = new HelpCenterDataProvider();
    }

    public function init()
    {
    }

    public function getData()
    {

        return '';
    }

    public function view()
    {
        $args = [
            'title'         => esc_html__('Help Center', 'wp-statistics'),
            'tooltip'       => esc_html__('Help Center', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug('help-center'),
            'data'          => $this->getData()
        ];

        Admin_Template::get_template(['layout/header', 'pages/help-center', 'layout/footer'], $args);
    }
}
