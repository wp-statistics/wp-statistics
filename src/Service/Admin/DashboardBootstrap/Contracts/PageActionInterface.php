<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Contracts;

/**
 * Interface for page action handlers.
 *
 * Each page in the Root dashboard should implement this interface to define
 * its specific AJAX actions and page name. This approach keeps actions organized
 * by page and maintains single responsibility principle.
 *
 * @since 15.0.0
 */
interface PageActionInterface
{
    /**
     * Get the page name.
     *
     * Returns a unique identifier for this page that will be used as the key
     * in the page handlers array.
     *
     * @return string The page name (e.g., 'overview', 'traffic', 'reports')
     */
    public function getPageName();

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

