<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Views;

use Exception;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseManagerDataProvider;

class TabsView extends BaseTabView
{
    protected $defaultTab = 'one';
    protected $tabs = [
        'one',
        'two',
        'three',
    ];

    public function __construct()
    {
        $this->dataProvider = new LicenseManagerDataProvider();
    }

    /**
     * Returns current selected tab/step.
     *
     * We've modified tabs for this class only and changed their key to `step`.
     *
     * @return string
     */
    protected function getCurrentTab()
    {
        return Request::get('step', $this->defaultTab);
    }

    /**
     * Is the given tab currently selected?
     *
     * We've modified tabs for this class only and changed their key to `step`.
     *
     * @param string $tab Tab slug.
     *
     * @return bool
     */
    protected function isTab($tab)
    {
        return Request::get('step', $this->defaultTab) === $tab;
    }

    /**
     * Returns the data for the given tab.
     *
     * @return mixed Returned data from a local method with a name like this: `getStep{$step}Data()`.
     */
    protected function getTabData()
    {
        $currentTab    = ucwords($this->getCurrentTab(), '-');
        $tabDataMethod = 'getStep' . str_replace('-', '', $currentTab) . 'Data';

        if (!method_exists($this, $tabDataMethod)) return [];

        return $this->$tabDataMethod();
    }

    /**
     * Returns data for step 1.
     *
     * @return array
     */
    public function getStepOneData()
    {
        return $this->dataProvider->getStepOneData();
    }

    /**
     * Returns data for step 2.
     *
     * @return array
     */
    public function getStepTwoData()
    {
        return $this->dataProvider->getStepTwoData();
    }

    /**
     * Returns data for step 3.
     *
     * @return array
     */
    public function getStepThreeData()
    {
        return $this->dataProvider->getStepThreeData();
    }

    public function render()
    {
        try {
            $currentTab = $this->getCurrentTab();
            $data       = $this->getTabData();

            $args = [
                'title'      => esc_html__('License Manager', 'wp-statistics'),
                'pageName'   => Menus::get_page_slug('license_manager'),
                'custom_get' => ['step' => $currentTab],
                'data'       => $data,
                'tabs'       => [
                    [
                        'link'  => Menus::admin_url('license_manager', ['step' => 'one']),
                        'title' => esc_html__('One', 'wp-statistics'),
                        'class' => $this->isTab('one') ? 'current' : '',
                    ],
                    [
                        'link'  => Menus::admin_url('license_manager', ['step' => 'two']),
                        'title' => esc_html__('Two', 'wp-statistics'),
                        'class' => $this->isTab('two') ? 'current' : '',
                    ],
                    [
                        'link'  => Menus::admin_url('license_manager', ['step' => 'three']),
                        'title' => esc_html__('Three', 'wp-statistics'),
                        'class' => $this->isTab('three') ? 'current' : '',
                    ],
                ]
            ];

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
            View::load("pages/license-manager/step-$currentTab", $args);
            Admin_Template::get_template(['layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}
