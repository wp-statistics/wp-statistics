<?php

namespace WP_Statistics\Service\CLI;

use WP_Statistics\Service\CLI\Commands\AnalyticsCommand;
use WP_Statistics\Service\CLI\Commands\DatabaseCommand;
use WP_Statistics\Service\CLI\Commands\DiagnosticCommand;
use WP_Statistics\Service\CLI\Commands\TrackCommand;

/**
 * CLI Manager for WP Statistics v15.
 *
 * Registers WP-CLI commands for managing WP Statistics from the command line.
 *
 * ## EXAMPLES
 *
 *      # Query analytics data
 *      $ wp statistics analytics query --source=visitors,views --group-by=date
 *
 *      # List available data sources
 *      $ wp statistics analytics list-sources
 *
 *      # Run diagnostic checks
 *      $ wp statistics diagnostic run
 *
 *      # Show database tables
 *      $ wp statistics db tables
 *
 *      # Reinitialize database
 *      $ wp statistics db reinitialize
 *
 *      # Track a hit
 *      $ wp statistics track --url="https://example.com"
 *
 * @since 15.0.0
 */
class CLIManager
{
    /**
     * Register CLI commands.
     *
     * @return void
     */
    public static function register()
    {
        if (!defined('WP_CLI') || !WP_CLI) {
            return;
        }

        \WP_CLI::add_command('statistics', __CLASS__);

        // Analytics: register class for subcommands (query, list-sources, list-groups, list-filters)
        \WP_CLI::add_command('statistics analytics', AnalyticsCommand::class);

        \WP_CLI::add_command('statistics diagnostic', DiagnosticCommand::class);
        \WP_CLI::add_command('statistics db', DatabaseCommand::class);
        \WP_CLI::add_command('statistics track', TrackCommand::class);
    }
}
