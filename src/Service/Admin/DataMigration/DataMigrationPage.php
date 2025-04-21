<?php

namespace WP_Statistics\Service\Admin\DataMigration;

use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Assets;
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
class DataMigrationPage extends BasePage
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
     * Constructor.
     *
     * Calls the parent constructor to ensure proper base page setup.
     */
    public function __construct()
    {
        parent::__construct();

        wp_localize_script(Admin_Assets::$react_dashboard_prefix, 'Wp_Statistics_Data_Migration_Object', $this->getData());
    }

    /**
     * Provides data to be passed to the React app via wp_localize_script.
     *
     * @return array Array of data exposed to the React frontend.
     */
    public function getData()
    {
        return [];
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
            'title'    => esc_html__('Data Migration', 'wp-statistics'),
            'pageName' => Menus::get_page_slug('data_migration'),
        ];

        Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load(['pages/data-migration/data-migration'], $args);
        // Admin_Template::get_template(['layout/footer'], $args);
    }
}
