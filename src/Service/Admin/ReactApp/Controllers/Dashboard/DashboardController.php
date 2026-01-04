<?php

namespace WP_Statistics\Service\Admin\ReactApp\Controllers\Dashboard;

use WP_Statistics\Abstracts\BaseDashboardController;
use WP_Statistics\Service\Admin\ReactApp\Views\DashboardPage;

/**
 * Controller for handling the Dashboard page.
 *
 * This controller is responsible for wiring the dashboard view
 * to the admin bootstrap. All analytics data requests now go through
 * the unified wp_statistics_analytics endpoint registered in
 * ReactAppManager::initGlobalAjax().
 *
 * The controller no longer manages page-specific AJAX actions, as these
 * have been replaced by the unified Analytics Query API using the
 * sources + group_by approach.
 *
 * @since 15.0.0
 */
class DashboardController extends BaseDashboardController
{
    /**
     * The view class for the Dashboard page.
     *
     * @var string|null
     */
    protected $pageView = DashboardPage::class;

    /**
     * Get AJAX actions handled by DashboardController.
     *
     * Returns empty array as all analytics queries now go through
     * the unified wp_statistics_analytics endpoint registered in
     * ReactAppManager::initGlobalAjax().
     *
     * @return array Empty array
     */
    public function getActions()
    {
        return [];
    }
}
