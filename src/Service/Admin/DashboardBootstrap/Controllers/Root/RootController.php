<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root;

use WP_Statistics\Abstracts\BaseDashboardController;
use WP_Statistics\Service\Admin\DashboardBootstrap\Views\Root;

/**
 * Controller for handling the Dashboard Root page.
 *
 * This controller is responsible for wiring the dashboard "Root" view
 * to the admin bootstrap. All analytics data requests now go through
 * the unified wp_statistics_analytics endpoint registered in
 * DashboardManager::initGlobalAjax().
 *
 * The controller no longer manages page-specific AJAX actions, as these
 * have been replaced by the unified Analytics Query API using the
 * sources + group_by approach.
 *
 * @since 15.0.0
 */
class RootController extends BaseDashboardController
{
    /**
     * The view class for the Dashboard Root page.
     *
     * @var string|null
     */
    protected $pageView = Root::class;

    /**
     * Get AJAX actions handled by RootController.
     *
     * Returns empty array as all analytics queries now go through
     * the unified wp_statistics_analytics endpoint registered in
     * DashboardManager::initGlobalAjax().
     *
     * @return array Empty array
     */
    public function getActions()
    {
        return [];
    }
}
