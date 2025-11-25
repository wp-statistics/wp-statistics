<?php
namespace WP_Statistics\Service\Admin\ExportImport\Reports;

use WP_Statistics\Service\Admin\AuthorAnalytics\AuthorAnalyticsDataProvider;
use WP_Statistics\Service\Admin\CategoryAnalytics\CategoryAnalyticsDataProvider;
use WP_Statistics\Service\Admin\Devices\DevicesDataProvider;
use WP_Statistics\Service\Admin\Exclusions\ExclusionsDataProvider;
use WP_Statistics\Service\Admin\Geographic\GeographicDataProvider;
use WP_Statistics\Service\Admin\PageInsights\PageInsightsDataProvider;
use WP_Statistics\Service\Admin\Referrals\ReferralsDataProvider;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsDataProvider;

class ReportsExportHandler
{
    public function init()
    {
        add_filter('wp_statistics_visitors_report_export_data', [$this, 'getVisitorsReportExportData'], 10, 3);
        add_filter('wp_statistics_pages_report_export_data', [$this, 'getPagesReportExportData'], 10, 3);
        add_filter('wp_statistics_referrals_report_export_data', [$this, 'getReferralsReportExportData'], 10, 3);
        add_filter('wp_statistics_category-analytics_report_export_data', [$this, 'getCategoryAnalyticsReportExportData'], 10, 3);
        add_filter('wp_statistics_author-analytics_report_export_data', [$this, 'getAuthorAnalyticsReportExportData'], 10, 3);
        add_filter('wp_statistics_geographic_report_export_data', [$this, 'getGeographicReportExportData'], 10, 3);
        add_filter('wp_statistics_devices_report_export_data', [$this, 'getDevicesReportExportData'], 10, 3);
        add_filter('wp_statistics_exclusions_report_export_data', [$this, 'getExclusionsReportExportData'], 10, 3);
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
            return ReportsExportDataTransformer::transformViewsData($data['data']);
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
        $dataProvider = new PageInsightsDataProvider($args);

        if ($report === 'top') {
            $data = $dataProvider->getTopData();
            return ReportsExportDataTransformer::transformPostsData($data['posts']);
        }

        if ($report === 'category') {
            $data = $dataProvider->getCategoryData();
            $data = array_values($data['categories'])[0] ?? [];
            return ReportsExportDataTransformer::transformCategoriesData($data);
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

    public function getReferralsReportExportData($data, $args, $report)
    {
        $dataProvider = new ReferralsDataProvider($args);

        if ($report === 'referred-visitors') {
            $data = $dataProvider->getReferredVisitors()['visitors'];
            return ReportsExportDataTransformer::transformVisitorsData($data);
        }

        if ($report === 'referrers') {
            $data = $dataProvider->getReferrers()['referrers'];
            return ReportsExportDataTransformer::transformReferrersData($data);
        }

        if ($report === 'search-engines') {
            $data = $dataProvider->getSearchEngineReferrals()['referrers'];
            return ReportsExportDataTransformer::transformReferrersData($data);
        }

        if ($report === 'social-media') {
            $data = $dataProvider->getSocialMediaReferrals()['referrers'];
            return ReportsExportDataTransformer::transformReferrersData($data);
        }

        if ($report === 'source-categories') {
            $data = $dataProvider->getSourceCategories();
            return ReportsExportDataTransformer::transformSourceCategoriesData($data);
        }

        return $data;
    }

    public function getCategoryAnalyticsReportExportData($data, $args, $report)
    {
        $dataProvider = new CategoryAnalyticsDataProvider($args);

        if ($report === 'report') {
            $data = $dataProvider->getCategoryReportData()['terms'];
            return ReportsExportDataTransformer::transformCategoriesData($data);
        }

        return $data;
    }

    public function getAuthorAnalyticsReportExportData($data, $args, $report)
    {
        $dataProvider = new AuthorAnalyticsDataProvider($args);

        if ($report === 'authors') {
            $data = $dataProvider->getAuthorsReportData()['authors'];
            return ReportsExportDataTransformer::transformAuthorsData($data);
        }

        return $data;
    }

    public function getGeographicReportExportData($data, $args, $report)
    {
        $dataProvider = new GeographicDataProvider($args);

        if ($report === 'countries') {
            $data = $dataProvider->getCountriesData()['countries'];
            return ReportsExportDataTransformer::transformGeoData('country', $data);
        }

        if ($report === 'cities') {
            $data = $dataProvider->getCitiesData()['cities'];
            return ReportsExportDataTransformer::transformGeoData('city', $data);
        }

        if ($report === 'europe') {
            $data = $dataProvider->getEuropeData()['countries'];
            return ReportsExportDataTransformer::transformGeoData('country', $data);
        }

        if ($report === 'regions') {
            $data = $dataProvider->getRegionsData()['regions'];
            return ReportsExportDataTransformer::transformGeoData('region', $data);
        }

        if ($report === 'us') {
            $data = $dataProvider->getUsData()['states'];
            return ReportsExportDataTransformer::transformGeoData('region', $data);
        }

        return $data;
    }

    public function getDevicesReportExportData($data, $args, $report)
    {
        $args = wp_parse_args($args, [
            // ...
        ]);

        $dataProvider = new DevicesDataProvider($args);

        if ($report === 'browsers') {
            $data = $dataProvider->getBrowsersData();
            return ReportsExportDataTransformer::transformDeviceData('browser', $data);
        }

        if ($report === 'platforms') {
            $data = $dataProvider->getPlatformsData();
            return ReportsExportDataTransformer::transformDeviceData('os', $data);
        }

        if ($report === 'models') {
            $data = $dataProvider->getModelsData();
            return ReportsExportDataTransformer::transformDeviceData('model', $data);
        }

        if ($report === 'categories') {
            $data = $dataProvider->getCategoriesData();
            return ReportsExportDataTransformer::transformDeviceData('device', $data);
        }

        if ($report === 'single-browser') {
            $data = $dataProvider->getSingleBrowserData();
            return ReportsExportDataTransformer::transformDeviceData('single-browser', $data);
        }

        return $data;
    }

    public function getExclusionsReportExportData($data, $args, $report)
    {
        $dataProvider = new ExclusionsDataProvider($args);
        $data = $dataProvider->getExclusionsData();
        return ReportsExportDataTransformer::transformExclusionsData($data['data'], $data['total']);
    }
}