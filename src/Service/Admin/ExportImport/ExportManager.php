<?php
namespace WP_Statistics\Service\Admin\ExportImport;

use WP_Statistics\Service\Admin\ExportImport\Reports\ReportsExportHandler;

class ExportManager
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'initExportHandlers']);
    }

    public function initExportHandlers()
    {
        $reportExportHandler = new ReportsExportHandler();
        $reportExportHandler->init();
    }
}