<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages;

use WP_Statistics\Models\CountryModel;
use WP_Statistics\Models\DeviceType;
use WP_Statistics\Models\OsModel;
use WP_Statistics\Models\SummaryModel;
use WP_Statistics\Models\SummaryTotalModel;
use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\PageActionInterface;

/**
 * Overview page action handler.
 *
 * This class manages all AJAX actions specific to the Overview page.
 * Each action method should be named after the action and will be
 * automatically callable through WordPress AJAX.
 *
 * @since 15.0.0
 */
class Overview implements PageActionInterface
{
    /**
     * Get the page name.
     *
     * @return string The page name used as key in page handlers array
     */
    public function getPageName()
    {
        return 'overview';
    }

    /**
     * Register AJAX actions for the Overview page.
     *
     * @return array<string, string> Mapping of action names to method names
     */
    public function registerActions()
    {
        return [
            'get_visitors_count' => 'getVisitorsCount',
            'get_views_count' => 'getViewsCount',
            'get_traffic_trends' => 'getTrafficTrends',
            'get_entry_reosurces' => 'getEntryReosurces',
            'get_top_devices' => 'getTopDevices',
            'get_top_oss' => 'getTopOss',
            'get_to_countries' => 'getTopCountries'
        ];
    }

    /**
     * Get overview statistics.
     *
     * Handles AJAX request to fetch overview statistics data
     * for the dashboard overview page.
     *
     * @return array Response data
     */
    public function getVisitorsCount()
    {
        $summaryTotalModel = new SummaryTotalModel();

        // Calculate date range for last 30 days (current period)
        $dateTo   = date('Y-m-d'); // Today
        $dateFrom = date('Y-m-d', strtotime('-30 days')); // 30 days ago

        // Calculate date range for previous 30 days (31 to 60 days ago)
        $prevDateTo   = date('Y-m-d', strtotime('-31 days')); // 31 days ago
        $prevDateFrom = date('Y-m-d', strtotime('-60 days')); // 60 days ago

        $result = $summaryTotalModel->getVisitorsCount([
            'date' => [
                'from' => $dateFrom, 
                'to'   => $dateTo
            ]
        ]);

        $prevResult = $summaryTotalModel->getVisitorsCount([
            'date' => [
                'from' => $prevDateFrom, 
                'to'   => $prevDateTo
            ]
        ]);

        // Calculate percentage change
        $changePercentage = $prevResult > 0 
            ? round((($result - $prevResult) / $prevResult) * 100, 2) 
            : 0;
        
        return [
            'current'    => $result,
            'previous'   => $prevResult,
            'precentage' => $changePercentage,
        ];
    }

    /**
     * Get views count statistics.
     *
     * Handles AJAX request to fetch views count data
     * for the dashboard overview page.
     *
     * @return array Response data
     */
    public function getViewsCount()
    {
        $summaryTotalModel = new SummaryTotalModel();

        // Calculate date range for last 30 days (current period)
        $dateTo   = date('Y-m-d'); // Today
        $dateFrom = date('Y-m-d', strtotime('-30 days')); // 30 days ago

        // Calculate date range for previous 30 days (31 to 60 days ago)
        $prevDateTo   = date('Y-m-d', strtotime('-31 days')); // 31 days ago
        $prevDateFrom = date('Y-m-d', strtotime('-60 days')); // 60 days ago

        $result = $summaryTotalModel->getViewsCount([
            'date' => [
                'from' => $dateFrom, 
                'to'   => $dateTo
            ]
        ]);

        $prevResult = $summaryTotalModel->getViewsCount([
            'date' => [
                'from' => $prevDateFrom, 
                'to'   => $prevDateTo
            ]
        ]);

        // Calculate percentage change
        $changePercentage = $prevResult > 0 
            ? round((($result - $prevResult) / $prevResult) * 100, 2) 
            : 0;
        
        return [
            'current'    => $result,
            'previous'   => $prevResult,
            'precentage' => $changePercentage,
        ];
    }

    /**
     * Get daily traffic trends (date & views & visitors).
     * 
     * @return array<string, array{date: string, views:int, visitors:int}> Date-keyed series
     */
    public function getTrafficTrends()
    {
        $summaryTotalModel = new SummaryTotalModel();

        $dateTo   = date('Y-m-d'); // Today
        $dateFrom = date('Y-m-d', strtotime('-3650 days')); // 30 days ago

        return $summaryTotalModel->getTrafficInRange([
            'date' => [
                'from' => $dateFrom, 
                'to'   => $dateTo
            ]
        ]);
    }

    /**
     * Get top entry resources.
     *
     * Handles AJAX request to fetch top resources (by views)
     * for the dashboard overview page.
     *
     * @return array Response data
     */
    public function getEntryReosurces()
    {
        $summaryModel = new SummaryModel();

        $dateTo   = date('Y-m-d'); // Today
        $dateFrom = date('Y-m-d', strtotime('-3650 days'));

        $results = $summaryModel->getTopViews([
            'date' => [
                'from' => $dateFrom, 
                'to'   => $dateTo
            ]
        ]);

        $data = [];

        foreach($results as $result) {
            $title = get_the_title($result->resource_id);

            $data[] = [
                'views'       => $result->views,
                'resource_id' => $result->resource_id,
                'title'       => $title
            ];
        }

        return $data;
    }

    /**
     * Get top device types.
     *
     * Handles AJAX request to fetch top device types (by views)
     * for the dashboard overview page.
     *
     * @return array Response data
     */
    public function getTopDevices()
    {
        $deviceModel = new DeviceType();
        $dateTo   = date('Y-m-d'); // Today
        $dateFrom = date('Y-m-d', strtotime('-30 days'));

        $results = $deviceModel->getTop([
            'date' => [
                'from' => $dateFrom, 
                'to'   => $dateTo
            ]
        ]);

        return $results;
    }

    /**
     * Get top operating systems.
     *
     * Handles AJAX request to fetch top operating systems (by views)
     * for the dashboard overview page.
     *
     * @return array Response data
     */
    public function getTopOss()
    {
        $osModel  = new OsModel();
        $dateTo   = date('Y-m-d'); // Today
        $dateFrom = date('Y-m-d', strtotime('-30 days'));

        return $osModel->getTop([
            'date' => [
                'from' => $dateFrom, 
                'to'   => $dateTo
            ]
        ]);
    }

    /**
     * Get top countries.
     *
     * Handles AJAX request to fetch top countries (by views)
     * for the dashboard overview page.
     *
     * @return array Response data
     */
    public function getTopCountries()
    {
        $countryModel = new CountryModel();
        $dateTo   = date('Y-m-d'); // Today
        $dateFrom = date('Y-m-d', strtotime('-30 days'));

        return $countryModel->getTop([
            'date' => [
                'from' => $dateFrom, 
                'to'   => $dateTo
            ]
        ]);
    }
}
