<?php

namespace WP_Statistics\Core\Operations;

use WP_Statistics\Core\AbstractCore;
use WP_STATISTICS\DB;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\Managers\TableHandler;

/**
 * Handles runtime registrations and the admin upgrade UI.
 *
 * Runs on load to register multisite create/drop handlers, add plugin row
 * meta links, and initialize the page-type updater, which displays an admin
 * notice and processes updates via AJAX until all records are typed.
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
        add_action('wpmu_new_blog', [$this, 'addTableOnCreateBlog'], 10, 1);
        add_filter('wpmu_drop_tables', [$this, 'removeTableOnDeleteBlog']);
        add_filter('plugin_row_meta', [$this, 'addMetaLinks'], 10, 2);
    }

    public function addTableOnCreateBlog($blogId)
    {
        if (!is_plugin_active_for_network(plugin_basename(WP_STATISTICS_MAIN_FILE))) {
            return;
        }

        $options = get_option(Option::$opt_name);
        switch_to_blog($blogId);
        TableHandler::createAllTables();
        update_option(Option::$opt_name, $options);
        restore_current_blog();
    }

    public function removeTableOnDeleteBlog($tables)
    {
        $tables[] = array_merge($tables, DB::table('all'));
        return $tables;
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