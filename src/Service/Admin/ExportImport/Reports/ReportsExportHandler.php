<?php
namespace WP_Statistics\Service\Admin\ExportImport\Reports;

use WP_Statistics\Service\Admin\PageInsights\PageInsightsDataProvider;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsDataProvider;

class ReportsExportHandler
{
    public function init()
    {
        add_filter('wp_statistics_visitors_report_export_data', [$this, 'getVisitorsReportExportData'], 10, 3);
        add_filter('wp_statistics_pages_report_export_data', [$this, 'getPagesReportExportData'], 10, 3);
    }

    public function getVisitorsReportExportData($data, $args, $report)
    {
        $dataProvider = new VisitorInsightsDataProvider($args);

        if ($report === 'visitors') {
            $data = $dataProvider->getVisitorsData();
            return ReportsExportDataTransformer::transformVisitorsData($data['data']);
        }

        if ($report === 'views') {
            $data = $dataProvider->getViewsData();
            return ReportsExportDataTransformer::transformVisitorsData($data['data']);
        }

        if ($report === 'online') {
            $data = $dataProvider->getOnlineVisitorsData();
            return ReportsExportDataTransformer::transformVisitorsData($data['data']);
        }

        if ($report === 'top-visitors') {
            $data = $dataProvider->getTopVisitorsData();
            return ReportsExportDataTransformer::transformVisitorsData($data['data']);
        }

        if ($report === 'logged-in-users') {
            $data = $dataProvider->getLoggedInUsersData();
            return ReportsExportDataTransformer::transformVisitorsData($data['data']);
        }

        return $data;
    }

    public function getPagesReportExportData($data, $args, $report)
    {
        $args = wp_parse_args($args, [
            'order'     => 'DESC',
            'taxonomy'  => 'category'
        ]);

        $dataProvider = new PageInsightsDataProvider($args);

        if ($report === 'top') {
            $data = $dataProvider->getTopData();
            return ReportsExportDataTransformer::transformPostsData($data['posts']);
        }

        if ($report === 'category') {
            $data = $dataProvider->getCategoryData();
            return ReportsExportDataTransformer::transformCategoriesData($data['categories']);
        }

        if ($report === 'author') {
            $data = $dataProvider->getAuthorsData();
            return ReportsExportDataTransformer::transformAuthorsData($data['authors']);
        }

        if ($report === '404') {
            $data = $dataProvider->get404Data();
            return $data['data'];
        }

        return $data;
    }
}