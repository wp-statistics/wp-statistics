<?php

namespace WP_Statistics\Components;

use WP_Statistics\Service\Admin\Settings\Definitions\SettingsAreaDefinitions;

/**
 * Context helper for WP Statistics options management.
 *
 * Provides static methods for retrieving, updating, and managing plugin options.
 * All settings are stored in the main `wp_statistics` option or in group options
 * (`wp_statistics_{group}`).
 *
 * @package WP_Statistics\Components
 * @since   15.0.0
 */
class Option extends Singleton
{
    /**
     * WP Statistics Basic Option name.
     *
     * @var string
     */
    public static string $optionName = 'wp_statistics';

    /**
     * Check if current context is network admin.
     *
     * @return bool
     */
    private static function isNetworkContext()
    {
        return is_multisite() && is_network_admin();
    }

    /**
     * Get option value using context-appropriate function.
     *
     * @param string $optionName Option name.
     * @param mixed  $default    Default value.
     * @return mixed
     */
    private static function getOptionValue($optionName, $default = array())
    {
        if (self::isNetworkContext()) {
            return get_site_option($optionName, $default);
        }

        return get_option($optionName, $default);
    }

    /**
     * Update option value using context-appropriate function.
     *
     * @param string $optionName Option name.
     * @param mixed  $value      Value to store.
     * @return bool
     */
    private static function updateOptionValue($optionName, $value)
    {
        if (self::isNetworkContext()) {
            return update_site_option($optionName, $value);
        }

        return update_option($optionName, $value);
    }

    /**
     * Delete option value using context-appropriate function.
     *
     * @param string $optionName Option name.
     * @return bool
     */
    private static function deleteOptionValue($optionName)
    {
        if (self::isNetworkContext()) {
            return delete_site_option($optionName);
        }

        return delete_option($optionName);
    }

    // =========================================================================
    // Default Options
    // =========================================================================

    /**
     * Get default options for WP Statistics.
     *
     * @return array Array of default option values.
     */
    public static function getDefaults()
    {
        $definitions = new SettingsAreaDefinitions();
        return $definitions->getDefaults();
    }

    // =========================================================================
    // Core Options (main wp_statistics option)
    // =========================================================================

    /**
     * Get all values stored in WP Statistics option.
     *
     * @return array Array of all option values.
     */
    public static function get()
    {
        $options = self::getOptionValue(self::$optionName, []);

        return (is_array($options)) ? $options : [];
    }

    /**
     * Update values to WP Statistics option.
     *
     * @param array $options Array of values to store in the option.
     * @return void
     */
    public static function update(array $options)
    {
        self::updateOptionValue(self::$optionName, $options);
    }

    /**
     * Get a single value from option.
     *
     * @param string $optionKey The option key to retrieve.
     * @param mixed|null $default Optional. Default value if option not found.
     * @return mixed The option value or default/false if not found.
     */
    public static function getValue(string $optionKey, $default = null)
    {
        $options = self::get();
        if (!array_key_exists($optionKey, $options)) {
            return $default !== null ? $default : false;
        }
        /**
         * Filters a For Return WP Statistics Option
         *
         * @param string $option Option name.
         * @param string $value Option Value.
         * @example add_filter('wp_statistics_option_coefficient', function(){ return 5; });
         */
        return apply_filters("wp_statistics_option_{$optionKey}", $options[$optionKey]);
    }

    /**
     * Update a single value in option.
     *
     * @param string $optionKey The option key to update.
     * @param mixed $value The new value for the option.
     * @return void
     */
    public static function updateValue(string $optionKey, $value)
    {
        $options = self::get();
        if (isset($options[$optionKey]) && $options[$optionKey] === $value) {
            return; // No update needed
        }
        $options[$optionKey] = $value;
        self::updateOptionValue(self::$optionName, $options);
    }

    // =========================================================================
    // Addon Options (deprecated — kept as stubs for legacy addon compatibility)
    // =========================================================================

    /**
     * @deprecated Legacy addon options. Returns false.
     */
    public static function getAddonValue(string $optionName, string $addonName = '', $default = null)
    {
        return $default ?? false;
    }

    /**
     * @deprecated Legacy addon options. No-op.
     */
    public static function updateAddon(array $options, string $addonName = '')
    {
    }

    /**
     * @deprecated Legacy addon options. Returns false.
     */
    public static function getAddon(string $addonName = '')
    {
        return false;
    }

    /**
     * @deprecated Legacy addon options. Returns false.
     */
    public static function getAddonOptions(string $addonName = '')
    {
        return false;
    }

    /**
     * @deprecated Legacy addon options. No-op.
     */
    public static function updateAddonOption(string $optionName, $value, string $addonName = '')
    {
    }

    // =========================================================================
    // Group Options (wp_statistics_{group})
    // =========================================================================

    /**
     * Get group option name.
     *
     * @param string $group The group name.
     * @return string The complete option name for the group.
     */
    private static function getGroupName(string $group)
    {
        return self::$optionName . "_{$group}";
    }

    /**
     * Get all values stored in group option.
     *
     * @param string $group The group name.
     * @return array Array of all values in the group option.
     */
    public static function getGroup(string $group)
    {
        $settingName = self::getGroupName($group);
        $options     = self::getOptionValue($settingName, []);
        $options     = is_array($options) ? $options : [];

        return apply_filters("wp_statistics_option_{$settingName}", $options);
    }

    /**
     * Get a single value from group option.
     *
     * @param string $group The group name.
     * @param string $optionKey The option key to retrieve.
     * @param mixed|null $default Optional. Default value if option not found.
     * @return mixed The value or default/false if not found.
     */
    public static function getGroupValue(string $group, string $optionKey, $default = null)
    {
        $settingName = self::getGroupName($group);
        $options     = self::getOptionValue($settingName, []);
        $options     = is_array($options) ? $options : [];

        $result = isset($options[$optionKey]) ? $options[$optionKey] : ($default ?? false);

        return apply_filters("wp_statistics_option_{$settingName}_{$optionKey}", $result);
    }

    /**
     * Update a single value to group option.
     *
     * @param string $optionKey The option key to update.
     * @param mixed $value The new value for the option.
     * @param string $group The group name.
     * @return void
     */
    public static function updateGroup(string $optionKey, $value, string $group)
    {
        $settingName = self::getGroupName($group);
        $options     = self::getOptionValue($settingName, []);
        $options     = is_array($options) ? $options : [];

        if (isset($options[$optionKey]) && $options[$optionKey] === $value) {
            return;
        }

        $options[$optionKey] = $value;
        self::updateOptionValue($settingName, $options);
    }

    /**
     * Delete a single value from group option.
     *
     * @param string $optionKey The option key to delete.
     * @param string $group The group name.
     * @return void
     */
    public static function deleteGroup(string $optionKey, string $group)
    {
        $settingName = self::getGroupName($group);
        $options     = self::getOptionValue($settingName, []);
        $options     = is_array($options) ? $options : [];

        if (!isset($options[$optionKey])) {
            return;
        }

        unset($options[$optionKey]);
        self::updateOptionValue($settingName, $options);
    }
}
