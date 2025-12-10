<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\DataSources;


use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\PageActionInterface;

/**
 * Geographics page action handler.
 *
 * This class manages all AJAX actions specific to the Geographics page.
 * Each action method should be named after the action and will be
 * automatically callable through WordPress AJAX.
 *
 * @since 15.0.0
 */
class Geographics implements PageActionInterface
{
    /**
     * Get the page name.
     *
     * @return string The page name used as key in page handlers array
     */
    public function getPageName()
    {
        return 'geographics';
    }

    /**
     * Register AJAX actions for the Geographics page.
     *
     * @return array<string, string> Mapping of action names to method names
     */
    public function registerActions()
    {
        return [];
    }
}
