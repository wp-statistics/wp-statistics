<?php

namespace WP_Statistics\Service\Admin\DataMigration;

/**
 * Manages the admin integration for the Data Migration feature.
 *
 * This class registers the "Data Migration" menu item within the WP Statistics admin menu.
 * It links the item to the corresponding React-based migration page.
 */
class DataMigrationManager
{
    /**
     * Class constructor.
     *
     * Hooks into the admin menu to register the Data Migration page.
     */
    public function __construct()
    {
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
    }

    /**
     * Adds the "Data Migration" item to the WP Statistics admin submenu.
     *
     * @param array $items Existing menu items.
     * @return array Modified menu items with the Data Migration entry added.
     */
    public function addMenuItem($items)
    {
        $items['data_migration'] = [
            'sub'      => 'settings',
            'title'    => esc_html__('Data Migration', 'wp-statistics'),
            'page_url' => 'data-migration',
            'callback' => DataMigrationPage::class,
            'priority' => 100,
        ];

        return $items;
    }
}