<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers;

use WP_Statistics\Abstracts\BaseDashboardController;
use WP_Statistics\Service\Admin\DashboardBootstrap\Views\MigrationPage;
use WP_Statistics\Utils\Request;

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
     * The page view.
     *
     * @var string|null
     */
    protected $pageView = MigrationPage::class;

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
        return [
            'start_migration',
        ];
    }

    /**
     * Start a new migration.
     *
     * @return array Empty array indicating success
     * @throws \Exception If invalid data is provided
     * @since 15.0.0
     */
    public function start_migration() {
        $type = Request::get('type');

        if (empty($type)) {
            throw new \Exception("Invalid Data");
        }

        return [];
    }
}
