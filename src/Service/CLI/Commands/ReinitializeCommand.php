<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_STATISTICS\Install;

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
     * ## EXAMPLES
     *
     *      # Reinitialize WP Statistics plugin
     *      $ wp statistics reinitialize
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        // Ensure required files are loaded
        if (!class_exists('WP_STATISTICS\Install')) {
            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';
        }

        WP_CLI::confirm('This will reinitialize the WP Statistics database tables. Are you sure?');

        Install::create_table(false);

        WP_CLI::success('Reinitialized WP Statistics Database!');
    }
}
