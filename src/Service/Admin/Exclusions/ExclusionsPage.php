<?php

namespace WP_Statistics\Service\Admin\Exclusions;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BasePage;

class ExclusionsPage extends BasePage
{
    protected $pageSlug = 'exclusions';

    public function init()
    {
        $this->disableScreenOption();
    }

    public function view()
    {
        $args = [
            'title'         => esc_html__('Excluded Data Overview', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug('exclusions'),
            'DateRang'      => Admin_Template::DateRange(),
            'hasDateRang'   => true
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/exclusions', 'layout/footer'], $args);
    }
}
