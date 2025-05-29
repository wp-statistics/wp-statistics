<?php

namespace WP_Statistics\Service\Admin\ExportImport;

use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

class ExportImportManager
{
    /**
     * ExportImportManager constructor.
     *
     * Hooks the REST API initialization action to register export/import REST routes.
     */
    public function __construct()
    {
        if ($this->isAddon('wp-statistics-customization')) {
            add_action('rest_api_init', array($this, 'registerRoutes'));

        }
    }

    /**
     * Registers the REST API routes by instantiating the ExportImportRestController.
     *
     * Called on the 'rest_api_init' WordPress action.
     *
     * @return void
     */
    public function registerRoutes()
    {
        $exportImportRestController = new ExportImportRestController();
        $exportImportRestController->registerRoutes();
    }

    /**
     * Check if a specific addon is active.
     *
     * @param string $addon The addon name.
     * @return bool True if the addon is active, false otherwise.
     */
    public function isAddon($addon)
    {
        $pluginHandler = new PluginHandler();
        return $pluginHandler->isPluginActive($addon);
    }

}