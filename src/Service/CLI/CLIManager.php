<?php

namespace WP_Statistics\Service\CLI;

use WP_Statistics\Service\CLI\Commands\SummaryCommand;
use WP_Statistics\Service\CLI\Commands\OnlineCommand;
use WP_Statistics\Service\CLI\Commands\VisitorsCommand;
use WP_Statistics\Service\CLI\Commands\ReinitializeCommand;
use WP_Statistics\Service\CLI\Commands\RecordCommand;

/**
 * CLI Manager for WP Statistics v15.
 *
 * Registers WP-CLI commands for managing WP Statistics from the command line.
 *
 * ## EXAMPLES
 *
 *      # Show summary of statistics
 *      $ wp statistics summary
 *
 *      # Get list of users online
 *      $ wp statistics online
 *
 *      # Show list of last visitors
 *      $ wp statistics visitors
 *
 *      # Reinitialize database
 *      $ wp statistics reinitialize
 *
 *      # Record a hit
 *      $ wp statistics record --url="https://example.com"
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
        \WP_CLI::add_command('statistics summary', SummaryCommand::class);
        \WP_CLI::add_command('statistics online', OnlineCommand::class);
        \WP_CLI::add_command('statistics visitors', VisitorsCommand::class);
        \WP_CLI::add_command('statistics reinitialize', ReinitializeCommand::class);
        \WP_CLI::add_command('statistics record', RecordCommand::class);
    }
}
