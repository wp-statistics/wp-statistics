<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers;

use WP_Statistics\Abstracts\BaseDashboardController;
use WP_Statistics\Service\Admin\DashboardBootstrap\Views\Root;

/**
 * Controller for handling the Dashboard Root page.
 *
 * RootController wires the dashboard "Root" view
 * to the admin bootstrap and extends the base dashboard controller.
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
     * Returns an array of action names that the Root dashboard page can handle.
     * These actions (if any) are registered with WordPress AJAX.
     *
     * @return array List of available AJAX actions for the Root page
     */
    public function getActions()
    {
        return [];
    }
}
