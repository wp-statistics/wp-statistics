<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;

/**
 * Manage WP Statistics cache.
 *
 * @since 15.0.0
 */
class CacheCommand
{
    /**
     * Clear the analytics query cache.
     *
     * Clears all cached analytics query results. This forces fresh data
     * to be fetched on the next request.
     *
     * ## OPTIONS
     *
     * [--yes]
     * : Skip confirmation prompt.
     *
     * ## EXAMPLES
     *
     *      # Clear analytics cache
     *      $ wp statistics cache clear
     *
     *      # Clear cache without confirmation
     *      $ wp statistics cache clear --yes
     *
     * @subcommand clear
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function clear($args, $assoc_args)
    {
        // Skip confirmation if --yes flag is provided
        if (!\WP_CLI\Utils\get_flag_value($assoc_args, 'yes', false)) {
            WP_CLI::confirm('This will clear all analytics cache. Continue?');
        }

        $handler = new AnalyticsQueryHandler(true);
        $cleared = $handler->clearCache();

        if ($cleared > 0) {
            WP_CLI::success(sprintf('Cleared %d cache entries.', $cleared));
        } else {
            WP_CLI::success('Cache is already empty.');
        }
    }

    /**
     * Show cache status and statistics.
     *
     * ## EXAMPLES
     *
     *      # Show cache status
     *      $ wp statistics cache status
     *
     * @subcommand status
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function status($args, $assoc_args)
    {
        global $wpdb;

        // Check transient cache entries
        $transientCount = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_wp_statistics_%'"
        );

        $siteTransientCount = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->options}
             WHERE option_name LIKE '_site_transient_wp_statistics_%'"
        );

        WP_CLI::line('WP Statistics Cache Status');
        WP_CLI::line('==========================');
        WP_CLI::line(sprintf('Transient cache entries: %d', $transientCount));
        WP_CLI::line(sprintf('Site transient cache entries: %d', $siteTransientCount));
        WP_CLI::line(sprintf('Total cached entries: %d', $transientCount + $siteTransientCount));
    }
}
