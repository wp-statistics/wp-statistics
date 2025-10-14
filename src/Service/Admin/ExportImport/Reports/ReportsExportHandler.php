<?php
namespace WP_Statistics\Service\Admin\ExportImport\Reports;

class ReportsExportHandler
{
    public function init()
    {
        add_filter('wp_statistics_report_export_data', [$this, 'getReportsExportData'], 10, 4);
    }

    public function getReportsExportData($data, $page, $report, $args)
    {
        $dataProvider = new ReportsExportDataProvider($page, $report, $args);
        return $dataProvider->getData();
    }
}