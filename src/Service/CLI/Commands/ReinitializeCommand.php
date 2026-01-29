<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Service\Database\Managers\TableHandler;

/**
 * Reinitialize WP Statistics database.
 *
 * @since 15.0.0
 */
class ReinitializeCommand
{
    /**
     * Reinitialize the WP Statistics database tables.
     *
     * This command recreates all WP Statistics database tables.
     * Use with caution as this may affect existing data.
     *
     * ## OPTIONS
     *
     * [--yes]
     * : Skip confirmation prompt. Useful for scripts and automation.
     *
     * ## EXAMPLES
     *
     *      # Reinitialize WP Statistics plugin (with confirmation)
     *      $ wp statistics reinitialize
     *
     *      # Reinitialize without confirmation (for scripts)
     *      $ wp statistics reinitialize --yes
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        // Skip confirmation if --yes flag is provided
        if (!\WP_CLI\Utils\get_flag_value($assoc_args, 'yes', false)) {
            WP_CLI::confirm('This will reinitialize the WP Statistics database tables. Are you sure?');
        }

        WP_CLI::line('Reinitializing database tables...');

        TableHandler::createAllTables();

        WP_CLI::success('Reinitialized WP Statistics Database!');
    }
}
