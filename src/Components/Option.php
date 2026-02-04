<?php

namespace WP_Statistics\Components;

use WP_Statistics\Utils\Environment;
use WP_Statistics\Utils\QueryParams;

/**
 * Context helper for WP Statistics options management.
 *
 * Provides static methods for retrieving, updating, and managing plugin options and user meta.
 * Handles both core plugin options and addon-specific settings.
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
     * WP Statistics Option name Prefix.
     *
     * @var string
     */
    public static string $optionPrefix = 'wps_';

    /**
     * WP Statistics Addon Option name Prefix.
     *
     * @var string
     */
    public static string $addonOptionPrefix = 'wpstatistics';

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
     * Uses get_site_option() for network admin context,
     * get_option() for everything else (works correctly in multisite
     * because WordPress handles the table prefix automatically).
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
     * Uses update_site_option() for network admin context,
     * update_option() for everything else.
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
     * Add option value using context-appropriate function.
     *
     * Uses add_site_option() for network admin context,
     * add_option() for everything else.
     *
     * @param string $optionName Option name.
     * @param mixed  $value      Value to store.
     * @return bool
     */
    private static function addOptionValue($optionName, $value)
    {
        if (self::isNetworkContext()) {
            return add_site_option($optionName, $value);
        }

        return add_option($optionName, $value);
    }

    /**
     * Delete option value using context-appropriate function.
     *
     * Uses delete_site_option() for network admin context,
     * delete_option() for everything else.
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
    // Standalone Options (prefixed, independent from main wp_statistics option)
    // =========================================================================

    /**
     * Get a standalone prefixed option value.
     *
     * Use this for storing separate options that aren't part of the main
     * wp_statistics option array.
     *
     * @param string $key     Option key (without prefix).
     * @param mixed  $default Default value.
     * @return mixed
     */
    public static function getOption(string $key, $default = null)
    {
        $optionName = self::getName($key);
        return self::getOptionValue($optionName, $default);
    }

    /**
     * Update a standalone prefixed option value.
     *
     * Use this for storing separate options that aren't part of the main
     * wp_statistics option array.
     *
     * @param string $key   Option key (without prefix).
     * @param mixed  $value Value to store.
     * @return bool
     */
    public static function updateOption(string $key, $value): bool
    {
        $optionName = self::getName($key);
        return self::updateOptionValue($optionName, $value);
    }

    /**
     * Delete a standalone prefixed option.
     *
     * Use this for deleting separate options that aren't part of the main
     * wp_statistics option array.
     *
     * @param string $key Option key (without prefix).
     * @return bool
     */
    public static function deleteOption(string $key): bool
    {
        $optionName = self::getName($key);
        return self::deleteOptionValue($optionName);
    }

    // =========================================================================
    // Default Options
    // =========================================================================

    /**
     * Get default options for WP Statistics.
     *
     * Returns an array of default plugin settings with predefined values.
     * These defaults are used when no custom values are set.
     *
     * @return array Array of default option values.
     */
    public static function getDefaults()
    {
        return [
            'query_params_allow_list'         => QueryParams::getDefaultAllowedList('string'),
            'anonymize_ips'                   => true,
            'hash_ips'                        => true,
            'geoip'                           => true,
            'useronline'                      => true,
            'pages'                           => true,
            'menu_bar'                        => true,
            'email_list'                      => Environment::getAdminEmail(),
            'time_report'                     => '0',
            'send_report'                     => 'mail',
            'geoip_license_type'              => 'js-deliver',
            'geoip_license_key'               => '',
            'geoip_dbip_license_key_option'   => '',
            'content_report'                  => '',
            'email_free_content_header'       => '',
            'email_free_content_footer'       => '',
            'update_geoip'                    => true,
            'privacy_audit'                   => true,
            'consent_level_integration'       => 'disabled',
            'anonymous_tracking'              => false,
            'do_not_track'                    => false,
            'exclude_administrator'           => true,
            'referrerspam'                    => true,
            'map_type'                        => 'jqvmap',
            'ip_method'                       => 'sequential',
            'exclude_loginpage'               => true,
            'exclude_404s'                    => false,
            'exclude_feeds'                   => true,
            'schedule_dbmaint'                => true,
            'schedule_dbmaint_days'           => '180',
            'charts_previous_period'          => true,
            'attribution_model'               => 'first-touch',
            'geoip_location_detection_method' => 'maxmind',
            'delete_data_on_uninstall'        => false,
            'share_anonymous_data'            => false,
            'display_notifications'           => true,
        ];
    }

    /**
     * Get complete option name with WP Statistics prefix.
     *
     * Prepends the plugin's option prefix to the given name.
     *
     * @param string $name The base option name.
     * @return string The complete prefixed option name.
     */
    public static function getName(string $name)
    {
        return self::$optionPrefix . $name;
    }

    /**
     * Get all values stored in WP Statistics option.
     *
     * Automatically uses appropriate WordPress function based on context:
     * - Network admin: get_site_option()
     * - Site admin: get_option()
     * - Cross-site query: get_blog_option()
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
     * Automatically uses appropriate WordPress function based on context:
     * - Network admin: update_site_option()
     * - Site admin: update_option()
     * - Cross-site query: update_blog_option()
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
     * Automatically uses network options when in network admin context.
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
     * Automatically uses appropriate WordPress function based on context:
     * - Network admin: update_site_option()
     * - Site admin: update_option()
     * - Cross-site query: update_blog_option()
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

    /**
     * Check if all option requirements are met.
     *
     * Verifies that all required options have their expected values.
     *
     * @param array $optionConfig The option configuration array.
     * @param string $conditionKey The key to check for requirements (default: 'require').
     * @return bool True if all requirements are met, false otherwise.
     */
    public static function meetsRequirements($optionConfig = [], $conditionKey = 'require')
    {
        if (empty($optionConfig[$conditionKey]) || !is_array($optionConfig[$conditionKey])) {
            return true;
        }

        foreach ($optionConfig[$conditionKey] as $optionKey => $shouldBeEnabled) {
            $actualValue = self::getValue($optionKey);
            if (($shouldBeEnabled && !$actualValue) || (!$shouldBeEnabled && $actualValue)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get addon option name.
     *
     * @param string $addonName The name of the addon.
     * @return string The complete option name for the addon.
     */
    private static function getAddonName(string $addonName)
    {
        return self::$addonOptionPrefix . "_{$addonName}_settings";
    }

    /**
     * Get all values stored in addon option.
     *
     * @param string $addonName The name of the addon.
     * @return array|false Array of addon option values or false if not found.
     */
    public static function getAddon(string $addonName = '')
    {
        $settingName = self::getAddonName($addonName);
        $options     = get_option($settingName);
        return (is_array($options)) ? $options : false;
    }

    /**
     * Get a single value from addon option.
     *
     * @param string $optionName The option name to retrieve.
     * @param string $addonName The name of the addon.
     * @param mixed|null $default Optional. Default value if option not found.
     * @return mixed The option value or default/false if not found.
     */
    public static function getAddonValue(string $optionName, string $addonName = '', $default = null)
    {
        $settingName = self::getAddonName($addonName);
        $options     = get_option($settingName);
        if (!is_array($options) || !array_key_exists($optionName, $options)) {
            return $default ?? false;
        }
        return apply_filters("wp_statistics_option_{$settingName}_{$optionName}", $options[$optionName]);
    }

    /**
     * Update values to addon option.
     *
     * @param array $options Array of values to store in the option.
     * @param string $addonName The name of the addon.
     * @return void
     */
    public static function updateAddon(array $options, string $addonName = '')
    {
        $settingName = self::getAddonName($addonName);
        update_option($settingName, $options);
    }

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
     * Automatically uses network options when in network admin context.
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
     * Automatically uses network options when in network admin context.
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
     * Automatically uses network options when in network admin context.
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
     * Add a single value to new group option.
     *
     * Automatically uses network options when in network admin context.
     *
     * @param string $optionKey The option key to add.
     * @param mixed $value The value for the new option.
     * @param string $group The group name.
     * @return void
     */
    public static function addGroup(string $optionKey, $value, string $group)
    {
        $settingName = self::getGroupName($group);
        $options     = self::getOptionValue($settingName, []);
        $options     = is_array($options) ? $options : [];

        $options[$optionKey] = $value;
        self::addOptionValue($settingName, $options);
    }

    /**
     * Delete a single value from group option.
     *
     * Automatically uses network options when in network admin context.
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
