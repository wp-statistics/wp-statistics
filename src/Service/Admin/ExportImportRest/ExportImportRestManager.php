<?php

namespace WP_Statistics\Service\Admin\ExportImportRest;

use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

class ExportImportRestManager
{
    /**
     * ExportImportManager constructor.
     *
     * Hooks the REST API initialization action to register export/import REST routes.
     */
    public function __construct()
    {
        if ($this->isAddon('wp-statistics-customization')) {
            $this->createExportRestController();
            $this->createImportRestController();
        }
    }

    /**
     * Create and return an instance of the export REST controller.
     *
     * This controller handles the /export-settings endpoint for exporting plugin settings.
     *
     * @return ExportRestController
     */
    public function createExportRestController()
    {
        return new ExportRestController();
    }

    /**
     * Create and return an instance of the import REST controller.
     *
     * This controller handles the /import-settings endpoint for importing plugin settings.
     *
     * @return ImportRestController
     */
    public function createImportRestController()
    {
        return new ImportRestController();
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