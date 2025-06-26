<?php

namespace WP_Statistics\Service\Admin\ExportImport;

class ExportImportManager
{
    /**
     * ExportImportManager constructor.
     *
     * Hooks the REST API initialization action to register export/import REST routes.
     */
    public function __construct()
    {
        $this->createExportRestController();
        $this->createImportRestController();
    }

    /**
     * Create and return an instance of the export REST controller.
     *
     * This controller handles the /export endpoint for exporting plugin settings.
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
     * This controller handles the /import endpoint for importing plugin settings.
     *
     * @return ImportRestController
     */
    public function createImportRestController()
    {
        return new ImportRestController();
    }
}