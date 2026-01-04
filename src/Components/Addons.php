<?php

namespace WP_Statistics\Components;

use WP_Statistics\Components\Option;

/**
 * Class responsible for handling plugin add-ons.
 *
 * Provides methods to check if an add-on is active and to compare
 * stored option values for active add-ons. Useful for controlling
 * conditional behavior based on add-on states and configurations.
 *
 * @package WP_Statistics\Components
 * @since 15.0.0
 */
class Addons
{
    /**
     * Check if an add‑on is active based on its slug.
     *
     * @param string $slug Add‑on slug (kebab‑case).
     * @return bool        True if the add‑on plugin is active.
     */
    public static function isActive($slug)
    {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $pluginName = sprintf(
            'wp-statistics-%1$s/wp-statistics-%1$s.php',
            esc_html($slug)
        );

        return is_plugin_active($pluginName);
    }

    /**
     * Compare an add‑on option to a given value, but only if the add‑on is active.
     *
     * @param string $addon Add‑on slug used for `isActive()`.
     * @param string $optionName Option key stored under the add‑on.
     * @param mixed $value Target value to compare against.
     * @param mixed|null $default Default when option is missing.
     * @param string $addonName Optional second name for
     *                                `Option::getAddonValue()`. When empty,
     *                                `$addon` is converted from kebab‑case
     *                                to snake_case and reused.
     * @return bool       True when add‑on is active **and** option matches.
     */
    public static function optionMatches($addon, $optionName, $value, $default = null, $addonName = '')
    {
        if (!self::isActive($addon)) {
            return false;
        }

        if ($addonName === '') {
            $addonName = str_replace('-', '_', $addon);
        }

        return Option::getAddonValue($optionName, $addonName, $default) === $value;
    }
}
