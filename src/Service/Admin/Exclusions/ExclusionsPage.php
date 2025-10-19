<?php

namespace WP_Statistics\Service\Admin\Exclusions;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Assets;
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;

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
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Exclusions_Object', $this->dataProvider->getChartData());

        return $this->dataProvider->getExclusionsData();
    }

    public function render()
    {
        $args = [
            'title'         => esc_html__('Excluded Data Overview', 'wp-statistics'),
            'tooltip'       => esc_html__('Track and analyze the logs of excluded traffic based on your Filtering & Exceptions settings.', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug('exclusions'),
            'DateRang'      => Admin_Template::DateRange(),
            'hasDateRang'   => true,
            'export'        => [ExportTypes::CSV_TABLE, ExportTypes::PDF_PAGE],
            'data'          => $this->getData()
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/exclusions', 'layout/footer'], $args);
    }
}
