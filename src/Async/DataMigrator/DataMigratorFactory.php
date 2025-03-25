<?php

namespace WP_Statistics\Async\DataMigrator;

use WP_STATISTICS\Admin_Assets;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

/**
 * Handles database migration initialization, notices, and script registrations.
 */
class DataMigratorFactory
{
    /**
     * Class constructor.
     * Initializes migration process if required.
     */
    public function __construct()
    {
        if (! $this->isValid()) {
            return;
        }

        add_action('admin_enqueue_scripts', [$this, 'registerScript']);
        add_filter('wp_statistics_ajax_list', [$this, 'addAjax']);

        $this->handleNotice();
    }

    /**
     * Checks if migration is required and the class exists.
     *
     * @return bool True if migration is required, false otherwise.
     */
    private function isValid()
    {
        if (! class_exists(MigrationManager::class)) {
            return false;
        }

        if (! MigrationManager::needsMigration()) {
            return false;
        }

        return true;
    }

    /**
     * Adds the migration process to the AJAX action list.
     *
     * @param array $list List of existing AJAX actions.
     * @return array Updated list including migration.
     */
    public function addAjax($list)
    {
        $list[] = [
            'class'  => self::getCurrentMigrate(),
            'action' => 'data_migrate',
            'public' => false
        ];

        return $list;
    }

    /**
     * Displays an admin notice to inform users about ongoing migration.
     */
    public function handleNotice()
    {

        $message = sprintf(
            '<div id="wp-statistics-migration-notice"><p><strong>%1$s</strong><br>%2$s</p></div>',
            esc_html__('WP Statistics: Process Running', 'wp-statistics'),
            sprintf(
                __('Database Migration is running in the background <strong class="remain-counter">(<span class="remain-number">%s</span> records remaining)</strong>. You can continue working or dismiss this notice.', 'wp-statistics'),
                0
            )
        );

        Notice::addNotice($message, 'data_migrate_status', 'info');
    }

    /**
     * Registers migration-related admin scripts.
     */
    public function registerScript()
    {
        wp_enqueue_script(
            Admin_Assets::$prefix,
            Admin_Assets::url('migrator.min.js'),
            ['jquery'],
            Admin_Assets::version(),
            ['in_footer' => true]
        );

        wp_localize_script(
            Admin_Assets::$prefix,
            'Wp_Statistics_Migrator_Data',
            [
                'rest_api_nonce' => wp_create_nonce('wp_rest'),
                'ajax_url'       => admin_url('admin-ajax.php')
            ]
        );
    }

    /**
     * Retrieves a specific migration version.
     *
     * @param string|null $version The migration version to retrieve.
     * @return mixed The migration instance.
     */
    public static function migrate($version = null)
    {
        return MigrationManager::getMigrations($version);
    }

    /**
     * Retrieves the currently required migration instance.
     *
     * @return mixed The current migration instance.
     */
    public static function getCurrentMigrate()
    {
        return MigrationManager::getMigration();
    }
}
