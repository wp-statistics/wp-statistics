<?php 

namespace WP_Statistics\Service\Admin\Geographic\Views;

use WP_Statistics\Abstracts\BaseTabView;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\Geographic\GeographicDataProvider;
use WP_Statistics\Utils\Request;

class TabsView extends BaseTabView
{
    protected $dataProvider;
    protected $defaultTab = 'countries';
    protected $tabs = [
        'countries',
        'cities',
        'europe',
        'us-states',
        'germany',
        'timezone'
    ];

    public function __construct()
    {
        parent::__construct();

        $this->dataProvider = new GeographicDataProvider([
            'from'  => Request::get('from', date('Y-m-d', strtotime('-1 month'))),
            'to'    => Request::get('to', date('Y-m-d')),
        ]);
    }

    public function getCountriesData()
    {
        return $this->dataProvider->getCountriesData();
    }

    public function getCitiesData()
    {
        return $this->dataProvider->getCitiesData();
    }

    public function render()
    {
        $currentTab = $this->getCurrentTab();
        $data       = $this->getTabData();

        $args = [
            'title'      => esc_html__('Geographic', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('geographic'),
            'paged'      => Admin_Template::getCurrentPaged(),
            'custom_get' => ['tab' => $currentTab],
            'DateRang'   => Admin_Template::DateRange(),
            'hasDateRang'=> true,
            'tabs'       => [
                [
                    'link'    => Menus::admin_url('geographic', ['tab' => 'countries']),
                    'title'   => esc_html__('Countries', 'wp-statistics'),
                    'tooltip' => esc_html__('Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'countries' ? 'current' : '',
                ],
                [
                    'link'    => Menus::admin_url('geographic', ['tab' => 'cities']),
                    'title'   => esc_html__('Cities', 'wp-statistics'),
                    'tooltip' => esc_html__('Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'cities' ? 'current' : '',
                ],
                [
                    'link'    => Menus::admin_url('geographic', ['tab' => 'europe']),
                    'title'   => esc_html__('European Countries', 'wp-statistics'),
                    'tooltip' => esc_html__('Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'europe' ? 'current' : '',
                ],
                [
                    'link'    => Menus::admin_url('geographic', ['tab' => 'us-states']),
                    'title'   => esc_html__('US States', 'wp-statistics'),
                    'tooltip' => esc_html__('Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'us-states' ? 'current' : '',
                ],
                [
                    'link'    => Menus::admin_url('geographic', ['tab' => 'germany']),
                    'title'   => esc_html__('Regions of Germany', 'wp-statistics'),
                    'tooltip' => esc_html__('Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'germany' ? 'current' : '',
                ],
                [
                    'link'    => Menus::admin_url('geographic', ['tab' => 'timezone']),
                    'title'   => esc_html__('Timezone', 'wp-statistics'),
                    'tooltip' => esc_html__('Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'timezone' ? 'current' : '',
                ],
            ],
        ];

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/geographic/$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
    }
}