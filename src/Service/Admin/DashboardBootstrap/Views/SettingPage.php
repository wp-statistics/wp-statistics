<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Views;

use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\Menu;
use WP_Statistics\Components\View;

/**
 * Handles rendering and data setup for the settings page.
 *
 * This class defines the structure, data, and view logic for the "Settings" page
 * within the WP Statistics admin interface. It extends the base page controller and
 * integrates with the React-based dashboard interface.
 * 
 * @since 15.0.0
 */
class SettingPage extends BasePage
{
    /**
     * Slug identifier for the admin page.
     *
     * Used internally to register and route the page.
     *
     * @var string
     */
    protected $pageSlug = 'new-settings';

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
    protected $pageIndex = 'new_settings';

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
        $this->pageTitle = esc_html__('Settings', 'wp-statistics');
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
     * Renders the settings page in the WordPress admin.
     *
     * This method:
     * - Localizes data for the React frontend.
     * - Loads the standard WP Statistics admin header and footer.
     * - Loads the page-specific view content.
     */
    public function view()
    {
        $args = [
            'title'    => $this->getPageTitle(),
            'tooltip'  => esc_html__(" "),
            'pageName' => Menu::buildPageSlug($this->getPageIndex()),
        ];

        // Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load(['pages/settings/settings'], $args);
        // Admin_Template::get_template(['layout/footer'], $args);
    }
}
