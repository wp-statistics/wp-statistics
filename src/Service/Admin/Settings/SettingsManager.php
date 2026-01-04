<?php

namespace WP_Statistics\Service\Admin\Settings;

/**
 * Settings Manager for v15 React-based Settings page.
 *
 * Handles Settings-specific AJAX endpoints for save/get operations.
 * Menu registration is handled by AdminMenuManager.
 * React assets are loaded by DashboardManager (shared SPA).
 *
 * @since 15.0.0
 */
class SettingsManager
{
    /**
     * @var SettingsAjaxHandler
     */
    private $ajaxHandler;

    /**
     * Initialize the Settings Manager.
     */
    public function __construct()
    {
        $this->initAjaxHandler();
    }

    /**
     * Initialize AJAX handlers for settings operations.
     *
     * @return void
     */
    private function initAjaxHandler()
    {
        $this->ajaxHandler = new SettingsAjaxHandler();
        $this->ajaxHandler->register();
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
