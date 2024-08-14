<?php

namespace WP_Statistics\Service\Admin\Devices\Views;

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Devices\DevicesDataProvider;

class TabsView extends BaseTabView
{
    protected $dataProvider;
    protected $defaultTab = 'browsers';
    protected $tabs       = [
        'browsers',
        'platforms',
        'models',
        'categories',
        'resolutions',
        'languages',
        'timezones'
    ];

    public function __construct()
    {
        parent::__construct();

        $this->dataProvider = new DevicesDataProvider([
            'per_page' => 10,
            'page'     => Admin_Template::getCurrentPaged()
        ]);
    }

    /**
     * Returns data for "Browsers" tab.
     *
     * @return  array
     */
    public function getBrowsersData()
    {
        return $this->dataProvider->getBrowsersData();
    }

    /**
     * Returns data for "Operating Systems" tab.
     *
     * @return  array
     */
    public function getPlatformsData()
    {
        return $this->dataProvider->getPlatformsData();
    }

    /**
     * Returns data for "Device Models" tab.
     *
     * @return  array
     */
    public function getModelsData()
    {
        return $this->dataProvider->getModelsData();
    }

    /**
     * Returns data for "Device Categories" tab.
     *
     * @return  array
     */
    public function getCategoriesData()
    {
        return $this->dataProvider->getCategoriesData();
    }

    /**
     * Returns the current tab's template.
     */
    public function render()
    {
        try {
            $currentTab  = $this->getCurrentTab();
            $data        = $this->getTabData();

            $args = [
                'title'           => esc_html__('Devices', 'wp-statistics'),
                'pageName'        => Menus::get_page_slug('devices'),
                'paged'           => Admin_Template::getCurrentPaged(),
                'custom_get'      => ['tab' => $currentTab],
                'DateRang'        => Admin_Template::DateRange(),
                'hasDateRang'     => true,
                'data'            => $data,
                'viewMoreUrlArgs' => ['type' => 'single-' . rtrim($currentTab, 's'), 'from' => Request::get('from'), 'to' => Request::get('to')],
                'tabs'            => [
                    [
                        'link'        => Menus::admin_url('devices', ['tab' => 'browsers']),
                        'title'       => esc_html__('Browsers', 'wp-statistics'),
                        'tooltip'     => esc_html__('Displays the different web browsers used by your visitors.', 'wp-statistics'),
                        'class'       => $this->isTab('browsers') ? 'current' : '',
                    ],
                    [
                        'link'        => Menus::admin_url('devices', ['tab' => 'platforms']),
                        'title'       => esc_html__('Operating Systems', 'wp-statistics'),
                        'tooltip'     => esc_html__('Shows the operating systems your visitors are using.', 'wp-statistics'),
                        'class'       => $this->isTab('platforms') ? 'current' : '',
                    ],
                    [
                        'link'        => Menus::admin_url('devices', ['tab' => 'models']),
                        'title'       => esc_html__('Device Models', 'wp-statistics'),
                        'tooltip'     => esc_html__('Provides data on the specific models of devices used by your visitors.', 'wp-statistics'),
                        'class'       => $this->isTab('models') ? 'current' : '',
                    ],
                    [
                        'link'        => Menus::admin_url('devices', ['tab' => 'categories']),
                        'title'       => esc_html__('Device Categories', 'wp-statistics'),
                        'tooltip'     => esc_html__('Displays visitor distribution across various device categories.', 'wp-statistics'),
                        'class'       => $this->isTab('categories') ? 'current' : '',
                    ],
                    [
                        'link'        => '#',
                        'title'       => esc_html__('Screen Resolutions', 'wp-statistics'),
                        'tooltip'     => esc_html__('Coming Soon', 'wp-statistics'),
                        'class'       => $this->isTab('resolutions') ? 'current' : '',
                        'coming_soon' => true,
                    ],
                    [
                        'link'        => '#',
                        'title'       => esc_html__('Languages', 'wp-statistics'),
                        'tooltip'     => esc_html__('Coming Soon', 'wp-statistics'),
                        'class'       => $this->isTab('languages') ? 'current' : '',
                        'coming_soon' => true,
                    ],
                    [
                        'link'        => '#',
                        'title'       => esc_html__('Timezones', 'wp-statistics'),
                        'tooltip'     => esc_html__('Coming Soon', 'wp-statistics'),
                        'class'       => $this->isTab('timezones') ? 'current' : '',
                        'coming_soon' => true,
                    ],
                ],
            ];

            if ($data['total'] > 0) {
                $args['total'] = $data['total'];

                $args['pagination'] = Admin_Template::paginate_links([
                    'item_per_page' => 10,
                    'total'         => $data['total'],
                    'echo'          => false
                ]);
            }

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/devices/$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
        } catch (\Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}
