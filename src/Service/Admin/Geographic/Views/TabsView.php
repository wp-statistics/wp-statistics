<?php 

namespace WP_Statistics\Service\Admin\Geographic\Views;

use Exception;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_STATISTICS\Country;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Geographic\GeographicDataProvider;

class TabsView extends BaseTabView
{
    protected $dataProvider;
    protected $defaultTab = 'countries';
    protected $tabs = [
        'countries',
        'cities',
        'europe',
        'us',
        'regions'
    ];

    public function __construct()
    {
        parent::__construct();

        $this->dataProvider = new GeographicDataProvider([
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged()
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
        try {
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
                        'link'    => Menus::admin_url('geographic', ['tab'   => 'countries']),
                        'title'   => esc_html__('Countries', 'wp-statistics'),
                        'tooltip' => esc_html__('Displays visitor counts from different countries.', 'wp-statistics'),
                        'class'   => $this->isTab('countries') ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('geographic', ['tab' => 'cities']),
                        'title'   => esc_html__('Cities', 'wp-statistics'),
                        'tooltip' => esc_html__('Displays visitor data based on their cities of origin.', 'wp-statistics'),
                        'class'   => $this->isTab('cities') ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('geographic', ['tab'   => 'europe']),
                        'title'   => esc_html__('European Countries', 'wp-statistics'),
                        'tooltip' => esc_html__('Displays visitor counts from European countries.', 'wp-statistics'),
                        'class'   => $this->isTab('europe') ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('geographic', ['tab'   => 'us']),
                        'title'   => esc_html__('US States', 'wp-statistics'),
                        'tooltip' => esc_html__('Displays visitor counts categorized by states within the USA.', 'wp-statistics'),
                        'class'   => $this->isTab('us') ? 'current' : '',
                    ]
                ],
            ];

            // If the country is US, or Unknown, hide region tab
            if ($countryCode && $countryCode != 'US') {
                $regionsTab = [
                    'link'    => Menus::admin_url('geographic', ['tab'   => 'regions']),
                    'title'   => sprintf(esc_html__('Regions of %s', 'wp-statistics'), Country::getName($countryCode)),
                    'tooltip' => esc_html__('Displays visitor statistics for regions within your website’s country, based on your website’s timezone setting.', 'wp-statistics'),
                    'class'   => $this->isTab('regions') ? 'current' : ''
                ];

                array_splice($args['tabs'], 4, 0, [$regionsTab]);
            }

            if ($data['total'] > 0) {
                $args['total'] = $data['total'];

                $args['pagination'] = Admin_Template::paginate_links([
                    'total' => $data['total'],
                    'echo'  => false
                ]);
            }

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/geographic/$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}