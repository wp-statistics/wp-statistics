<?php

namespace WP_Statistics\Core;

use WP_STATISTICS\Option;

/**
 * Base class containing shared initialization logic and utilities for core components.
 *
 * Provides helpers for option setup, required file loading, version checks and updates,
 * and enforces an `execute()` method that concrete subclasses must implement.
 *
 * @package WP_Statistics\Core
 */
abstract class AbstractCore
{
    /**
     * Stores the current plugin version retrieved from the database.
     *
     * @var string
     */
    protected $currentVersion;

    /**
     * Stores the latest version of the plugin defined by the codebase.
     *
     * @var string
     */
    protected $latestVersion;

    /**
     * Whether operations are being performed network-wide (multisite network activation).
     *
     * @var bool
     */
    protected $networkWide = false;

    /**
     * WordPress database access object.
     *
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * AbstractCore constructor.
     *
     * @return void
     */
    public function __construct($networkWide = false)
    {
        $this->latestVersion = WP_STATISTICS_VERSION;
        $this->networkWide   = (bool)$networkWide;

        $this->setWpdb();
    }

    /**
     * Initialize the wpdb property from the global $wpdb.
     */
    private function setWpdb()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
    }

    /**
     * Initialize default options.
     *
     * @return void
     */
    protected function initializeDefaultOptions()
    {
        $this->loadRequiredFiles();

        $options = get_option(Option::$opt_name);
        if (empty($options) || !is_array($options)) {
            update_option(Option::$opt_name, Option::defaultOption());
        }
    }

    /**
     * Load required files.
     *
     * @return void
     */
    private function loadRequiredFiles()
    {
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-template.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-online.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visitor.php';
    }

    /**
     * Checks whether the plugin is a fresh installation.
     *
     * @return void
     */
    protected function checkIsFresh()
    {
        $version = get_option('wp_statistics_plugin_version');

        if (empty($version)) {
            update_option('wp_statistics_is_fresh', true);
        } else {
            update_option('wp_statistics_is_fresh', false);
        }

        $installationTime = get_option('wp_statistics_installation_time');

        if (empty($installationTime)) {
            update_option('wp_statistics_installation_time', time());
        }
    }

    /**
     * Checks whether the plugin is updated.
     *
     * @return bool
     */
    protected function isUpdated()
    {
        $this->currentVersion = get_option('wp_statistics_plugin_version');

        return $this->currentVersion != $this->latestVersion;
    }

    /**
     * Update the plugin version.
     *
     * @return void
     */
    protected function updateVersion()
    {
        update_option('wp_statistics_plugin_version', $this->latestVersion);
    }

    /**
     * Execute the core function.
     *
     * @return void
     */
    abstract public function execute();
}
