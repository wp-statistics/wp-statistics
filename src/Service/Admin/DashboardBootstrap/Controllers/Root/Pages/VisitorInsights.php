<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages;

use stdClass;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Models\CountryModel;
use WP_Statistics\Models\ReferrerModel;
use WP_Statistics\Models\SummaryTotalModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\PageActionInterface;
use WP_Statistics\Utils\Query;

/**
 * Visitor Insight page action handler.
 *
 * This class manages all AJAX actions specific to the Visitor Insight page.
 * Each action method should be named after the action and will be
 * automatically callable through WordPress AJAX.
 *
 * @since 15.0.0
 */
class VisitorInsights implements PageActionInterface
{
    /**
     * Get the page name.
     *
     * @return string The page name used as key in page handlers array
     */
    public function getPageName()
    {
        return 'visitor_insight';
    }

    /**
     * Register AJAX actions for the Visitor In page.
     *
     * @return array<string, string> Mapping of action names to method names
     */
    public function registerActions()
    {
        return [
            'get_overview_data' => 'getOverviewData',
            'get_most_active_visitors' => 'getMostActiveVisitors',
        ];
    }

    public function getOverviewData()
    {
        $summaryTotalModel = new SummaryTotalModel();
        $visitorsModel     = new VisitorsModel();
        $viewsModel        = new ViewsModel();
        $countryModel      = new CountryModel();
        $referrerModel     = new ReferrerModel();

        // Calculate date range for last 30 days (current period)
        $dateTo   = date('Y-m-d'); // Today
        $dateFrom = date('Y-m-d', strtotime('-30 days')); // 30 days ago

        // Calculate date range for previous 30 days (31 to 60 days ago)
        $prevDateTo   = date('Y-m-d', strtotime('-31 days')); // 31 days ago
        $prevDateFrom = date('Y-m-d', strtotime('-60 days')); // 60 days ago
        
        $result = $summaryTotalModel->getFieldsCount([
            'fields' => [
                'SUM(views) as views',
                'SUM(visitors) as visitors',
            ],
            'date' => [
                'from' => $dateFrom, 
                'to'   => $dateTo
            ]
        ]);

        if (DateTime::isTodayOrFutureDate($dateTo)) {
            $result->visitors += $visitorsModel->count();
            $result->views += $viewsModel->countDaily();
        }

        if ($dateFrom !== $dateTo && DateTime::isTodayOrFutureDate($dateFrom)) {
            $result->visitors += $visitorsModel->count();
            $result->views += $viewsModel->countDaily();
        }

        $prevResult = $summaryTotalModel->getFieldsCount([
            'fields' => [
                'SUM(views) as views',
                'SUM(visitors) as visitors',
            ],
            'date' => [
                'from' => $prevDateFrom, 
                'to'   => $prevDateTo
            ]
        ]);

        $visitorsChangePercentage = 0;
        $viewsChangePercentage = 0;
        $currentVisitors  = ! empty($result->visitors) ? $result->visitors : 0;
        $previousVisitors = ! empty($prevResult->visitors) ? $prevResult->visitors : 0;
        $currentViews     = ! empty($result->views) ? $result->views : 0;
        $previousViews    = ! empty($prevResult->views) ? $prevResult->views : 0;

        if ($currentVisitors > 0) {
            $visitorsChangePercentage = $previousVisitors > 0 
                ? round((($currentVisitors - $previousVisitors) / $previousVisitors) * 100, 2) 
                : 0;
        }

        if ($currentViews > 0) {
            $viewsChangePercentage = $previousViews > 0 
                ? round((($currentViews - $previousViews) / $previousViews) * 100, 2) 
                : 0;
        }
        
        $topCountry = $countryModel->getTop([
            'date' => [
                'from' => $dateFrom, 
                'to'   => $dateTo
            ],
            'limit' => 1
        ]);

        $topReferrer = $referrerModel->getTop([
            'date' => [
                'from' => $dateFrom, 
                'to'   => $dateTo
            ],
            'limit' => 1
        ]);

        return [
            'visitors' => [
                'current'    => $currentVisitors,
                'previous'   => $previousVisitors,
                'precentage' => $visitorsChangePercentage,
            ],
            'views' => [
                'current'    => $currentViews,
                'previous'   => $previousViews,
                'precentage' => $viewsChangePercentage,
            ],
            'top_country' => ! empty($topCountry[0]) ? $topCountry[0] : [],
            'top_referrer' => ! empty($topReferrer[0]) ? $topReferrer[0] : [],
        ];
    }

    /**
     * Get most active visitors.
     *
     * Fetches the most active visitors based on their total views.
     * For each visitor, finds their sessions and aggregates the total views,
     * then returns visitor details including browser, country, city, referrer, 
     * entry page and exit page.
     *
     * @return array Response data containing visitor details
     */
    public function getMostActiveVisitors()
    {
        $visitorsModel = new VisitorsModel();

        // Calculate date range for last 30 days
        $dateTo   = date('Y-m-d 23:59:59');
        $dateFrom = date('Y-m-d 00:00:00', strtotime('-30 days'));
        
        return $visitorsModel->getMostActiveVisitors([
            'date' => [
                'from' => $dateFrom, 
                'to'   => $dateTo
            ]
        ]);
    }
}
