<?php

namespace WP_Statistics\Service\Admin\Settings;

use WP_Statistics\Bootstrap;

/**
 * Main orchestrator for v15 React-based Settings page.
 *
 * Coordinates the Settings page registration, AJAX handlers,
 * and settings data providers.
 *
 * @since 15.0.0
 */
class SettingsManager
{
    /**
     * @var SettingsController
     */
    private $controller;

    /**
     * @var SettingsAjaxHandler
     */
    private $ajaxHandler;

    /**
     * Initialize the Settings Manager.
     */
    public function __construct()
    {
        $this->initController();
        $this->initAjaxHandler();
        $this->initMenuFilter();
    }

    /**
     * Hook into the menu system to use v15 Settings controller.
     *
     * @return void
     */
    private function initMenuFilter()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'modifySettingsMenu'], 20);
    }

    /**
     * Modify the settings menu to use v15 controller.
     *
     * @param array $menuList Current menu list.
     * @return array Modified menu list.
     */
    public function modifySettingsMenu($menuList)
    {
        // Only modify if v15 mode is active
        if (!Bootstrap::isV15()) {
            return $menuList;
        }

        // Update settings menu to use v15 controller
        if (isset($menuList['settings'])) {
            $menuList['settings']['callback'] = SettingsController::class;
        }

        return $menuList;
    }

    /**
     * Initialize the page controller.
     *
     * @return void
     */
    private function initController()
    {
        $this->controller = new SettingsController();
    }

    /**
     * Initialize AJAX handlers.
     *
     * @return void
     */
    private function initAjaxHandler()
    {
        $this->ajaxHandler = new SettingsAjaxHandler();
        $this->ajaxHandler->register();
    }

    /**
     * Get the controller instance.
     *
     * @return SettingsController
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get the AJAX handler instance.
     *
     * @return SettingsAjaxHandler
     */
    public function getAjaxHandler()
    {
        return $this->ajaxHandler;
    }
}
