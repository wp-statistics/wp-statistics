<?php

namespace WP_Statistics\Service\Admin\Exclusions;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Assets;

class ExclusionsPage extends BasePage
{
    protected $pageSlug = 'exclusions';
    protected $dataProvider;

    public function __construct()
    {
        parent::__construct();

        $this->dataProvider = new ExclusionsDataProvider();
    }

    public function init()
    {
        $this->disableScreenOption();
    }

    public function getData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Exclusions_Object', [
            'exclusions_chart_data' => $this->dataProvider->getChartData()
        ]);

        return $this->dataProvider->getExclusionsData();
    }

    public function view()
    {
        $args = [
            'title'         => esc_html__('Excluded Data Overview', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug('exclusions'),
            'DateRang'      => Admin_Template::DateRange(),
            'hasDateRang'   => true,
            'data'          => $this->getData()
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/exclusions', 'layout/footer'], $args);
    }
}