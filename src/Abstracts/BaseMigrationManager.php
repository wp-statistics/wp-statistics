<?php

namespace WP_Statistics\Abstracts;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_STATISTICS\User;

/**
 * Abstract base class for managing migration-related operations.
 *
 * This abstract class centralizes common functionality needed by migration
 * managers, such as security checks and context validation.
 */
abstract class BaseMigrationManager
{
    /**
     * Validates whether the current admin page and user have access to handle migration-related functionality.
     *
     * This method performs security checks to ensure that:
     * - The current user has the sufficient perimissions
     * - The current page is a WP Statistics plugin page
     *
     * @return bool True if the context is valid for migration operations, false otherwise
     */
    protected function isValidMigrationContext()
    {
        if (!User::checkUserCapability(Option::get('manage_capability', 'manage_options'))) {
            return false;
        }

        if (Menus::in_plugin_page()) {
            return true;
        }

        return false;
    }

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
