<?php

namespace WP_Statistics\Abstracts;

use WP_STATISTICS\Option;
use WP_Statistics\Traits\MigrationAccess;
use WP_STATISTICS\User;

/**
 * Abstract base class for managing migration-related operations.
 *
 * This abstract class centralizes common functionality needed by migration
 * managers, such as security checks and context validation.
 */
abstract class BaseMigrationManager
{
    use MigrationAccess;

    /**
     * Ensures the current user has permission to run migration-related operations.
     *
     * If the user lacks the required capability defined by the plugin option
     * `manage_capability` (defaulting to `manage_options`), execution will halt with
     * a 403 response.
     *
     * @return void
     */
    protected function verifyMigrationPermission()
    {
        if (User::checkUserCapability(Option::get('manage_capability', 'manage_options'))) {
            return;
        }

        wp_die(
            __('You do not have sufficient permissions to run the ajax migration process.', 'wp-statistics'),
            __('Permission Denied', 'wp-statistics'),
            [
                'response' => 403
            ]
        );
    }
}
