<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Views;

use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;

/**
 * Handles rendering and data setup for the Data Migration admin page.
 *
 * This class defines the structure, data, and view logic for the "Data Migration" page
 * within the WP Statistics admin interface. It extends the base page controller and
 * integrates with the React-based dashboard interface.
 */
class MigrationPage extends BasePage
{
    /**
     * Slug identifier for the admin page.
     *
     * Used internally to register and route the page.
     *
     * @var string
     */
    protected $pageSlug = 'data-migration';

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
    protected $pageIndex = 'data_migration';

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
        $this->pageTitle = esc_html__('Data Migration', 'wp-statistics');
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
     * Renders the Data Migration page in the WordPress admin.
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
            'tooltip'      => esc_html__(" "),
            'pageName' => Menus::get_page_slug($this->getPageIndex()),
        ];

        Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load(['pages/data-migration/data-migration'], $args);
        Admin_Template::get_template(['layout/footer'], $args);
    }
}
