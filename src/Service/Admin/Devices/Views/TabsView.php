<?php

namespace WP_Statistics\Service\Admin\Devices\Views;

use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Devices\DevicesDataProvider;

class TabsView extends BaseTabView
{
    protected $dataProvider;
    protected $defaultTab = 'overview';
    protected $tabs       = [
        'overview',
        'browsers',
        'platforms',
        'models',
        'categories'
    ];

    public function __construct()
    {
        parent::__construct();

        $this->dataProvider = new DevicesDataProvider([
            'per_page' => 10,
            'page'     => Admin_Template::getCurrentPaged()
        ]);
    }

    public function getOverviewData()
    {
        return $this->dataProvider->getOverviewData();
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
                    'id'          => 'overview',
                    'link'        => Menus::admin_url('devices', ['tab' => 'overview']),
                    'title'       => esc_html__('Overview', 'wp-statistics'),
                    'class'       => $this->isTab('overview') ? 'current' : '',
                    'export'      => [ExportTypes::PDF_PAGE]
                ],
                [
                    'id'          => 'browsers',
                    'link'        => Menus::admin_url('devices', ['tab' => 'browsers']),
                    'title'       => esc_html__('Browsers', 'wp-statistics'),
                    'tooltip'     => esc_html__('Visitors by browser.', 'wp-statistics'),
                    'class'       => $this->isTab('browsers') ? 'current' : '',
                    'export'      => [ExportTypes::CSV_TABLE, ExportTypes::PDF_PAGE]
                ],
                [
                    'id'          => 'platforms',
                    'link'        => Menus::admin_url('devices', ['tab' => 'platforms']),
                    'title'       => esc_html__('Operating Systems', 'wp-statistics'),
                    'tooltip'     => esc_html__('Visitors by operating system.', 'wp-statistics'),
                    'class'       => $this->isTab('platforms') ? 'current' : '',
                    'export'      => [ExportTypes::CSV_TABLE, ExportTypes::PDF_PAGE]
                ],
                [
                    'id'          => 'models',
                    'link'        => Menus::admin_url('devices', ['tab' => 'models']),
                    'title'       => esc_html__('Device Models', 'wp-statistics'),
                    'tooltip'     => esc_html__('Visitors by device model.', 'wp-statistics'),
                    'class'       => $this->isTab('models') ? 'current' : '',
                    'export'      => [ExportTypes::CSV_TABLE, ExportTypes::PDF_PAGE]

                ],
                [
                    'id'          => 'categories',
                    'link'        => Menus::admin_url('devices', ['tab' => 'categories']),
                    'title'       => esc_html__('Device Categories', 'wp-statistics'),
                    'tooltip'     => esc_html__('Visitors by device type (desktop, phone, tablet).', 'wp-statistics'),
                    'class'       => $this->isTab('categories') ? 'current' : '',
                    'export'      => [ExportTypes::CSV_TABLE, ExportTypes::PDF_PAGE]
                ]
            ],
        ];

        if (isset($data['total']) && $data['total'] > 0) {
            $args['total'] = $data['total'];

            $args['pagination'] = Admin_Template::paginate_links([
                'item_per_page' => 10,
                'total'         => $data['total'],
                'echo'          => false
            ]);
        }

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/devices/$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
    }
}
