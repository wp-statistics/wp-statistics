<?php

namespace WP_Statistics\Core\Operations;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Core\AbstractCore;
use WP_Statistics\Core\CoreFactory;
use WP_STATISTICS\DB;
use WP_STATISTICS\Option;

/**
 * Handles runtime registrations and the admin upgrade UI.
 *
 * Runs on every request to register the multisite create/drop handlers and
 * the plugin row meta links.
 *
 * @package WP_Statistics\Core\Operations
 */
class Loader extends AbstractCore
{
    /**
     * Loader constructor.
     *
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
        // Site lifecycle hooks. They are registered on every request (not only in wp-admin),
        // so sites created or deleted by WP-CLI, the REST API or front-end signup are covered.
        add_action('wp_initialize_site', [$this, 'setupNewSite'], 20, 1);
        add_filter('wpmu_drop_tables', [$this, 'removeTableOnDeleteBlog'], 10, 2);
        add_filter('plugin_row_meta', [$this, 'addMetaLinks'], 10, 2);
    }

    /**
     * Sets up WP Statistics on a site that was just created, when the plugin is
     * network-activated: tables, default options and stored version.
     *
     * The new site starts from the options of the site it was created from (as before),
     * then runs the same per-site steps as a network activation.
     *
     * @param \WP_Site|int $site New site object (wp_initialize_site) or ID.
     * @return void
     */
    public function setupNewSite($site)
    {
        $blogId = $site instanceof \WP_Site ? (int)$site->blog_id : (int)$site;

        if ($blogId <= 0 || !self::isNetworkActive()) {
            return;
        }

        $options = get_option(Option::$opt_name);

        switch_to_blog($blogId);

        if (is_array($options) && !empty($options)) {
            update_option(Option::$opt_name, $options);
        }

        CoreFactory::activator(false);

        restore_current_blog();
    }

    /**
     * Back-compat alias for the old wpmu_new_blog callback.
     *
     * @param int $blogId
     * @return void
     * @deprecated 14.17.0 Use setupNewSite().
     */
    public function addTableOnCreateBlog($blogId)
    {
        $this->setupNewSite($blogId);
    }

    /**
     * Adds the WP Statistics tables of a site to the list WordPress drops when the
     * site is deleted. WordPress has already switched to that site, so the table
     * names carry the site's own prefix.
     *
     * @param string[] $tables Table names WordPress will drop.
     * @param int $blogId Site ID being deleted.
     * @return string[]
     */
    public function removeTableOnDeleteBlog($tables, $blogId = 0)
    {
        return self::addSiteTables($tables);
    }

    /**
     * Adds the current site's WP Statistics table names to a list of tables.
     *
     * @param string[] $tables
     * @return string[]
     */
    public static function addSiteTables($tables)
    {
        $tables = is_array($tables) ? $tables : [];

        // Every known table name, whether it exists or not: WordPress drops them
        // with DROP TABLE IF EXISTS, so missing add-on tables are skipped.
        foreach (array_unique(DB::$db_table) as $name) {
            $table = DB::getTableName($name);

            if (!in_array($table, $tables, true)) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * Whether the plugin is network-activated.
     *
     * @return bool
     */
    private static function isNetworkActive()
    {
        if (!is_multisite()) {
            return false;
        }

        if (!function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active_for_network(plugin_basename(WP_STATISTICS_MAIN_FILE));
    }

    public function addMetaLinks($links, $file)
    {
        if ($file !== plugin_basename(WP_STATISTICS_MAIN_FILE)) {
            return $links;
        }

        $pluginUrl = 'https://wordpress.org/plugins/wp-statistics/';
        $links[]   = '<a href="' . esc_url($pluginUrl) . '" target="_blank" title="' . esc_attr__('Click here to visit the plugin on WordPress.org', 'wp-statistics') . '">' . esc_html__('Visit WordPress.org page', 'wp-statistics') . '</a>';
        $rateUrl   = 'https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post';
        $links[]   = '<a href="' . esc_url($rateUrl) . '" target="_blank" title="' . esc_attr__('Click here to rate and review this plugin on WordPress.org', 'wp-statistics') . '">' . esc_html__('Rate this plugin', 'wp-statistics') . '</a>';

        return $links;
    }
}