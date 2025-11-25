<?php

namespace WP_Statistics\Service\Admin\ExportImport;

use WP_Statistics\Service\Admin\ExportImport\Reports\ReportsExportHandler;

class ExportImportManager
{
    public function __construct()
    {
        add_action('init', [$this, 'registerRestControllers']);
        add_action('rest_api_init', [$this, 'initExportHandlers']);
    }

    /**
     * Register an instance of the export/import REST controllers.
     *
     * This controller handles the /import and /export endpoint
     */
    public function registerRestControllers()
    {
        $exportRestController = new ExportRestController();
        $importRestController = new ImportRestController();
    }

    /**
     * Initializes the reports export handler.
     */
    public function initExportHandlers()
    {
        $reportsExportHandler = new ReportsExportHandler();
        $reportsExportHandler->init();
    }
}