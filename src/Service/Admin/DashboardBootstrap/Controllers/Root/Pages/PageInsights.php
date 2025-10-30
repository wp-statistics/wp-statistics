<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Controllers\Root\Pages;


use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\PageActionInterface;

/**
 * Page Insights page action handler.
 *
 * This class manages all AJAX actions specific to the Page Insights page.
 * Each action method should be named after the action and will be
 * automatically callable through WordPress AJAX.
 *
 * @since 15.0.0
 */
class PageInsights implements PageActionInterface
{
    /**
     * Get the page name.
     *
     * @return string The page name used as key in page handlers array
     */
    public function getPageName()
    {
        return 'page_insight';
    }

    /**
     * Register AJAX actions for the Page Insights page.
     *
     * @return array<string, string> Mapping of action names to method names
     */
    public function registerActions()
    {
        return [];
    }
}
