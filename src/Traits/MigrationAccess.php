<?php
namespace WP_Statistics\Traits;

use WP_Statistics\Option;
use WP_Statistics\User;
use WP_Statistics\Menus;

/**
 * Trait: MigrationAccess
 *
 * Minimal, stateless check used by migration-related classes to validate the
 * access context. It only verifies capability (from the option
 * `manage_capability`, defaulting to `manage_options`) and that the current
 * admin page belongs to the WP Statistics plugin.
 *
 * Note: Nonce/CSRF validation and other context rules should be handled
 * separately (e.g., in controllers or managers) to keep this trait focused.
 */
trait MigrationAccess
{
    /**
     * True if the current request is allowed to run migration operations.
     *
     * @return bool
     */
    protected function isValidContext()
    {
        if (! User::checkUserCapability(Option::get('manage_capability', 'manage_options'))) {
            return false;
        }

        return Menus::in_plugin_page();
    }
}
