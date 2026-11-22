<?php

namespace WP_Statistics\Service\Admin\Geographic\Views;

use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_STATISTICS\Country;
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;
use WP_Statistics\Service\Admin\Geographic\GeographicDataProvider;

class TabsView extends BaseTabView
{
    protected $dataProvider;
    protected $defaultTab = 'overview';
    protected $tabs = [
        'overview',
        'countries',
        'cities',
        'europe',
        'us',
        'regions'
    ];

    public function __construct()
    {
        // The Regions tab needs the site's country (from the timezone); without it, the tab is hidden and not allowed
        if (!Helper::getTimezoneCountry()) {
            $this->tabs = array_values(array_diff($this->tabs, ['regions']));
        }

        parent::__construct();

        $this->dataProvider = new GeographicDataProvider([
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged()
        ]);
    }

    public function getOverviewData()
    {
        $data       = $this->dataProvider->getOverviewData();
        $chartData  = $this->dataProvider->getOverviewChartData();

        // Send map chart to the view as well
        $data['map_data'] = $chartData['map_chart_data'];

        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Geographic_Object', $chartData);

        return $data;
    }

    public function getCountriesData()
    {
        return $this->dataProvider->getCountriesData();
    }

    public function getCitiesData()
    {
        return $this->dataProvider->getCitiesData();
    }

    public function getEuropeData()
    {
        return $this->dataProvider->getEuropeData();
    }

    public function getUsData()
    {
        return $this->dataProvider->getUsData();
    }

    public function getRegionsData()
    {
        return $this->dataProvider->getRegionsData();
    }

    public function render()
    {
        $currentTab     = $this->getCurrentTab();
        $data           = $this->getTabData();
        $countryCode    = Helper::getTimezoneCountry();

        $args = [
            'title'      => esc_html__('Geographic', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('geographic'),
            'paged'      => Admin_Template::getCurrentPaged(),
            'custom_get' => ['tab' => $currentTab],
            'DateRang'   => Admin_Template::DateRange(),
            'hasDateRang'=> true,
            'data'       => $data,
            'tabs'       => [
                [
                    'id'      => 'overview',
                    'link'    => Menus::admin_url('geographic', ['tab'   => 'overview']),
                    'title'   => esc_html__('Overview', 'wp-statistics'),
                    'class'   => $this->isTab('overview') ? 'current' : '',
                    'export'  => [ExportTypes::CSV_METRICS, ExportTypes::PDF_PAGE]
                ],
                [
                    'id'      => 'countries',
                    'link'    => Menus::admin_url('geographic', ['tab'   => 'countries']),
                    'title'   => esc_html__('Countries', 'wp-statistics'),
                    'tooltip' => esc_html__('Visitors by country.', 'wp-statistics'),
                    'class'   => $this->isTab('countries') ? 'current' : '',
                    'export'  => [ExportTypes::CSV_TABLE, ExportTypes::PDF_PAGE]
                ],
                [
                    'id'      => 'cities',
                    'link'    => Menus::admin_url('geographic', ['tab' => 'cities']),
                    'title'   => esc_html__('Cities', 'wp-statistics'),
                    'tooltip' => esc_html__('Visitors by city.', 'wp-statistics'),
                    'class'   => $this->isTab('cities') ? 'current' : '',
                    'export'  => [ExportTypes::CSV_TABLE, ExportTypes::PDF_PAGE]
                ],
                [
                    'id'      => 'europe',
                    'link'    => Menus::admin_url('geographic', ['tab'   => 'europe']),
                    'title'   => esc_html__('European Countries', 'wp-statistics'),
                    'tooltip' => esc_html__('Visitors by European country.', 'wp-statistics'),
                    'class'   => $this->isTab('europe') ? 'current' : '',
                    'export'  => [ExportTypes::CSV_TABLE, ExportTypes::PDF_PAGE]
                ],
                [
                    'id'      => 'us',
                    'link'    => Menus::admin_url('geographic', ['tab'   => 'us']),
                    'title'   => esc_html__('US States', 'wp-statistics'),
                    'tooltip' => esc_html__('Visitors by US state.', 'wp-statistics'),
                    'class'   => $this->isTab('us') ? 'current' : '',
                    'export'  => [ExportTypes::CSV_TABLE, ExportTypes::PDF_PAGE]
                ]
            ],
        ];

        // If the country is US, or Unknown, hide region tab
        if ($countryCode && $countryCode != 'US') {
            $regionsTab = [
                'id'      => 'regions',
                'link'    => Menus::admin_url('geographic', ['tab'   => 'regions']),
                /* translators: %s: string value */
                'title'   => sprintf(esc_html__('Regions of %s', 'wp-statistics'), Country::getName($countryCode)),
                'tooltip' => esc_html__('Visitors by region of your site’s country (taken from the site timezone).', 'wp-statistics'),
                'class'   => $this->isTab('regions') ? 'current' : '',
                'export'  => [ExportTypes::CSV_TABLE, ExportTypes::PDF_PAGE]
            ];

            array_splice($args['tabs'], 4, 0, [$regionsTab]);
        }

        if (isset($data['total']) && $data['total'] > 0) {
            $args['total'] = $data['total'];

            $args['pagination'] = Admin_Template::paginate_links([
                'total' => $data['total'],
                'echo'  => false
            ]);
        }

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/geographic/$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
    }
}