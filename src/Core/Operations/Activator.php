<?php

namespace WP_Statistics\Core\Operations;

use WP_Statistics\Core\AbstractCore;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\Managers\SchemaMaintainer;
use WP_Statistics\Service\Database\Managers\TableHandler;

/**
 * Handles plugin activation tasks.
 *
 * Runs on activation (and per site when network-activated on multisite) to check
 * fresh-install state, create required tables, mark background processes to start,
 * ensure default options exist, and store the current plugin version.
 *
 * @see register_activation_hook()
 * @package WP_Statistics\Core\Operations
 */
class Activator extends AbstractCore
{
    /**
     * Activator constructor.
     *
     * @param bool $networkWide
     * @return void
     */
    public function __construct($networkWide = false)
    {
        parent::__construct($networkWide);
        $this->execute();
    }

    /**
     * Execute the core function.
     *
     * @return void
     */
    public function execute()
    {
        $this->loadRequiredFiles();

        if (is_multisite() && $this->networkWide) {
            $blogIds = $this->wpdb->get_col("SELECT `blog_id` FROM {$this->wpdb->blogs}");
            foreach ($blogIds as $blogId) {
                switch_to_blog($blogId);
                $this->checkIsFresh();
                TableHandler::createAllTables();
                restore_current_blog();
            }
        } else {
            $this->checkIsFresh();
            TableHandler::createAllTables();
        }

        $this->markBackgroundProcessAsInitiated();
        $this->createOptions();
        $this->initializeVersion();
        SchemaMaintainer::repair(true);
    }

    /**
     * Ensures the plugin version is stored in the database.
     *
     * If the version option does not already exist, this method will
     * initialize it by calling updateVersion().
     *
     * @return void
     */
    private function initializeVersion() {
        $version = get_option('wp_statistics_plugin_version');

        if (!empty($version)) {
            return;
        }

        $this->updateVersion();
    }

    /**
     * Creates the default options if they do not already exist.
     *
     * @return bool
     */
    private function createOptions()
    {
        $existedOption = get_option(Option::$opt_name);
        if ($existedOption === false || (isset($existedOption) and !is_array($existedOption))) {
            update_option(Option::$opt_name, Option::defaultOption());
        }
    }

    /**
     * Checks background processes during a fresh installation.
     *
     * @return void
     */
    private function markBackgroundProcessAsInitiated()
    {
        Option::deleteOptionGroup('data_migration_process_started', 'jobs');

        if ($this->isUpdated()) {
            return;
        }

        Option::saveOptionGroup('update_source_channel_process_initiated', true, 'jobs');
        Option::saveOptionGroup('update_geoip_process_initiated', true, 'jobs');
        Option::saveOptionGroup('schema_migration_process_started', true, 'jobs');
        Option::saveOptionGroup('table_operations_process_initiated', true, 'jobs');
        Option::saveOptionGroup('word_count_process_initiated', true, 'jobs');
    }

    /**
     * Load core plugin classes required during activation.
     *
     * We include them explicitly here because activation runs in a minimal context and some
     * runtime bootstrapping (like conditional autoloaders) may not yet be in place.
     *
     * @return void
     * @todo Remove this method after the included files are migrated to PSR-4 autoloading.
     */
    private function loadRequiredFiles()
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visitor.php';
    }
}