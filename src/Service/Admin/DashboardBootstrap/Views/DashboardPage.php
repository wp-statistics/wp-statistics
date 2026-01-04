<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Views;

use WP_Statistics\Abstracts\BasePage;
use WP_Statistics\Components\Menu;
use WP_Statistics\Components\View;

/**
 * Handles rendering and data setup for the Dashboard admin page.
 *
 * This class defines the structure, data, and view logic for the dashboard page
 * within the WP Statistics admin interface. It extends the base page controller and
 * integrates with the React-based dashboard interface.
 *
 * @since 15.0.0
 */
class DashboardPage extends BasePage
{
    /**
     * Slug identifier for the admin page.
     *
     * Used internally to register and route the page.
     *
     * @var string
     */
    protected $pageSlug = 'dashboard';

    /**
     * The page title.
     *
     * @var string
     */
    protected $pageTitle = '';

    /**
     * The page index.
     *
     * @var string
     */
    protected $pageIndex = 'dashboard';

    /**
     * Constructor.
     *
     * Calls the parent constructor to ensure proper base page setup.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setPageTitle();
    }

    /**
     * Set the page title.
     *
     * @return void
     */
    private function setPageTitle()
    {
        $this->pageTitle = '';
    }

    /**
     * Get the page title.
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * Get the page slug.
     *
     * @return string
     */
    public function getPageSlug()
    {
        return $this->pageSlug;
    }

    /**
     * Get the page index.
     *
     * @return string
     */
    public function getPageIndex()
    {
        return $this->pageIndex;
    }

    /**
     * Get the page priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return 100;
    }

    /**
     * Renders the Dashboard page in the WordPress admin.
     *
     * This method:
     * - Prepares data to be localized for the React frontend.
     * - Loads the standard WP Statistics admin header and footer.
     * - Loads the Dashboard page view content.
     */
    public function view()
    {
        $args = [
            'title'    => $this->getPageTitle(),
            'tooltip'  => '',
            'pageName' => Menu::buildPageSlug($this->getPageIndex()),
        ];

        View::load(['pages/dashboard/index'], $args);
    }
}
