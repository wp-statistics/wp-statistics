<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\DataSources;

use WP_Statistics\Service\Admin\DashboardBootstrap\Abstracts\AbstractAnalyticsPage;

/**
 * Visitor Insight page action handler.
 *
 * This class manages all AJAX actions specific to the Visitor Insight page.
 * Each action method should be named after the action and will be
 * automatically callable through WordPress AJAX.
 *
 * @since 15.0.0
 */
class VisitorInsights extends AbstractAnalyticsPage
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
     * Register AJAX actions for the Visitor Insight page.
     *
     * @return array<string, string> Mapping of action names to method names
     */
    public function registerActions()
    {
        return [
            'get_initial_data' => 'getInitialData',
            'get_widget_data'  => 'getWidgetData',
        ];
    }

    /**
     * Get initial data for the Visitor Insights page.
     *
     * Receives the query structure from React widget and passes it to AnalyticsQuery.
     *
     * @return array Query result data
     */
    public function getInitialData()
    {
        return $this->executeQueryFromRequest();
    }

    /**
     * Get widget-specific data (e.g., Traffic Trends with different timeframes).
     *
     * @return array Query result data
     */
    public function getWidgetData()
    {
        return $this->executeQueryFromRequest();
    }
}
