<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Contracts;

/**
 * Interface for page action handlers and global endpoint handlers.
 *
 * This interface can be implemented by:
 * - Page-specific action handlers (organized by page)
 * - Global endpoint handlers (application-wide endpoints)
 *
 * This approach keeps actions organized and maintains single responsibility principle.
 *
 * @since 15.0.0
 */
interface PageActionInterface
{
    /**
     * Get the endpoint/handler name.
     *
     * Returns a unique identifier for this handler. Can represent:
     * - A page name for page-specific handlers (e.g., 'overview', 'traffic')
     * - An endpoint name for global handlers (e.g., 'analytics', 'export')
     *
     * @return string The handler identifier
     */
    public function getEndpointName();

    /**
     * Register AJAX actions for this page.
     *
     * Returns a mapping of AJAX action names (in snake_case) to their corresponding
     * PHP method names (in camelCase) that will be automatically registered
     * with WordPress AJAX handling system through the RootController.
     *
     * Example:
     * ```php
     * return [
     *     'get_overview_stats' => 'getOverviewStats',
     *     'update_overview_settings' => 'updateOverviewSettings'
     * ];
     * ```
     *
     * @return array<string, string> Mapping of action names to method names
     */
    public function registerActions();
}

