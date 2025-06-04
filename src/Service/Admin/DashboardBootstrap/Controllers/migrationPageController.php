<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers;

use WP_Statistics\Abstracts\BaseDashboardController;
use WP_Statistics\Option;

/**
 * Controller for handling data migration page.
 *
 * This controller provides endpoints for managing and monitoring the data migration process:
 * - Getting current migration status
 * - Starting a new migration
 * - Monitoring migration progress
 *
 * @since 15.0.0
 */
class MigrationPageController extends BaseDashboardController
{
    /**
     * Get list of available AJAX actions.
     *
     * Returns an array of action names that this controller can handle.
     * These actions are automatically registered with WordPress AJAX.
     *
     * @return array List of available AJAX actions
     * @since 15.0.0
     */
    public function getActions()
    {
        return [];
    }
}
