<?php

namespace WP_Statistics\Service\Options;

/**
 * Option Manager for WP Statistics v15.
 *
 * Provides option management with in-memory caching for optimal performance.
 * Replaces legacy WP_STATISTICS\Option class.
 *
 * @since 15.0.0
 */
class OptionManager
{
    /**
     * Main option name.
     */
    public const OPTION_NAME = 'wp_statistics';

    /**
     * Option prefix for prefixed options.
     */
    public const OPTION_PREFIX = 'wps_';

    /**
     * Cached options.
     *
     * @var array|null
     */
    private static $optionsCache = null;

    /**
     * Cached user options per user ID.
     *
     * @var array
     */
    private static $userOptionsCache = [];

    /**
     * Cached option groups.
     *
     * @var array
     */
    private static $groupCache = [];

    /**
     * Cached addon options.
     *
     * @var array
     */
    private static $addonCache = [];

    /**
     * Get option value.
     *
     * @param string $key     Option key.
     * @param mixed  $default Default value if not set.
     * @return mixed Option value.
     */
    public static function get(string $key, $default = null)
    {
        $options = self::getAll();

        if (!array_key_exists($key, $options)) {
            return $default ?? false;
        }

        /**
         * Filters a WP Statistics option value.
         *
         * @param mixed  $value Option value.
         * @param string $key   Option key.
         */
        return apply_filters("wp_statistics_option_{$key}", $options[$key]);
    }

    /**
     * Set option value.
     *
     * @param string $key   Option key.
     * @param mixed  $value Option value.
     * @return bool Success.
     */
    public static function set(string $key, $value): bool
    {
        $options       = self::getAll();
        $options[$key] = $value;

        $result = update_option(self::OPTION_NAME, $options);

        // Invalidate cache
        self::$optionsCache = null;

        return $result;
    }

    /**
     * Delete an option.
     *
     * @param string $key Option key.
     * @return bool Success.
     */
    public static function delete(string $key): bool
    {
        $options = self::getAll();

        if (!array_key_exists($key, $options)) {
            return false;
        }

        unset($options[$key]);

        $result = update_option(self::OPTION_NAME, $options);

        // Invalidate cache
        self::$optionsCache = null;

        return $result;
    }

    /**
     * Check if option exists.
     *
     * @param string $key Option key.
     * @return bool Whether option exists.
     */
    public static function has(string $key): bool
    {
        $options = self::getAll();
        return array_key_exists($key, $options);
    }

    /**
     * Get all options (cached).
     *
     * @return array All options.
     */
    public static function getAll(): array
    {
        if (self::$optionsCache === null) {
            $options = get_option(self::OPTION_NAME);
            self::$optionsCache = is_array($options) ? $options : [];
        }

        return self::$optionsCache;
    }

    /**
     * Save all options at once.
     *
     * @param array $options Options array.
     * @return bool Success.
     */
    public static function saveAll(array $options): bool
    {
        $result = update_option(self::OPTION_NAME, $options);
        self::$optionsCache = null;
        return $result;
    }

    /**
     * Get prefixed option name.
     *
     * @param string $name Option name.
     * @return string Prefixed name.
     */
    public static function getPrefixedName(string $name): string
    {
        return self::OPTION_PREFIX . $name;
    }

    /**
     * Get default options.
     *
     * @return array Default options.
     */
    public static function getDefaults(): array
    {
        return [
            'robotlist'                       => '',
            'query_params_allow_list'         => '',
            'store_ip'                        => false,
            'hash_rotation_interval'          => 'daily',
            'geoip'                           => true,
            'useronline'                      => true,
            'pages'                           => true,
            'email_list'                      => get_bloginfo('admin_email'),
            'use_cache_plugin'                => true,
            'time_report'                     => '0',
            'send_report'                     => 'mail',
            'geoip_license_type'              => 'js-deliver',
            'geoip_license_key'               => '',
            'content_report'                  => '',
            'privacy_audit'                   => true,
            'consent_level_integration'       => 'disabled',
            'anonymous_tracking'              => false,
            'exclude_administrator'           => true,
            'ip_method'                       => 'sequential',
            'exclude_loginpage'               => true,
            'exclude_404s'                    => false,
            'exclude_feeds'                   => true,
            'schedule_dbmaint'                => true,
            'schedule_dbmaint_days'           => '180',
            'data_retention_mode'             => 'forever',
            'data_retention_days'             => '180',
            'geoip_location_detection_method' => 'maxmind',
            'delete_data_on_uninstall'        => false,
            'share_anonymous_data'            => false,
            'display_notifications'           => true,
            'word_count_analytics'            => true,
            'show_privacy_issues_in_report'   => false,
            'disable_column'                  => false,
            'menu_bar'                        => true,
        ];
    }

    // =========================================================================
    // User Options
    // =========================================================================

    /**
     * Get user-specific option.
     *
     * @param string   $key     Option key.
     * @param mixed    $default Default value.
     * @param int|null $userId  User ID (null for current user).
     * @return mixed Option value.
     */
    public static function getUserOption(string $key, $default = null, ?int $userId = null)
    {
        $userId = $userId ?? get_current_user_id();

        if ($userId === 0) {
            return false;
        }

        $userOptions = self::getAllUserOptions($userId);

        if (!array_key_exists($key, $userOptions)) {
            return $default ?? false;
        }

        return $userOptions[$key];
    }

    /**
     * Set user-specific option.
     *
     * @param string   $key    Option key.
     * @param mixed    $value  Option value.
     * @param int|null $userId User ID (null for current user).
     * @return bool Success.
     */
    public static function setUserOption(string $key, $value, ?int $userId = null): bool
    {
        $userId = $userId ?? get_current_user_id();

        if ($userId === 0) {
            return false;
        }

        $userOptions       = self::getAllUserOptions($userId);
        $userOptions[$key] = $value;

        $result = update_user_meta($userId, self::OPTION_NAME, $userOptions);

        // Invalidate cache
        unset(self::$userOptionsCache[$userId]);

        return $result !== false;
    }

    /**
     * Get all user options (cached).
     *
     * @param int $userId User ID.
     * @return array User options.
     */
    public static function getAllUserOptions(int $userId): array
    {
        if (!isset(self::$userOptionsCache[$userId])) {
            $options = get_user_meta($userId, self::OPTION_NAME, true);
            self::$userOptionsCache[$userId] = is_array($options) ? $options : [];
        }

        return self::$userOptionsCache[$userId];
    }

    // =========================================================================
    // Option Groups
    // =========================================================================

    /**
     * Get option from a named group.
     *
     * @param string      $group   Group name.
     * @param string|null $key     Option key (null for all group options).
     * @param mixed       $default Default value.
     * @return mixed Option value or all group options.
     */
    public static function getGroup(string $group, ?string $key = null, $default = null)
    {
        $settingName = "wp_statistics_{$group}";

        if (!isset(self::$groupCache[$group])) {
            $options = get_option($settingName);
            self::$groupCache[$group] = is_array($options) ? $options : [];
        }

        $options = self::$groupCache[$group];

        if ($key === null) {
            return apply_filters("wp_statistics_option_{$settingName}", $options);
        }

        if (!array_key_exists($key, $options)) {
            return $default ?? false;
        }

        return apply_filters("wp_statistics_option_{$settingName}", $options[$key]);
    }

    /**
     * Set option in a named group.
     *
     * @param string $group Group name.
     * @param string $key   Option key.
     * @param mixed  $value Option value.
     * @return bool Success.
     */
    public static function setGroup(string $group, string $key, $value): bool
    {
        $settingName = "wp_statistics_{$group}";

        // Force reload from database
        unset(self::$groupCache[$group]);
        $options = self::getGroup($group);

        $options[$key] = $value;

        $result = update_option($settingName, $options);

        // Invalidate cache
        unset(self::$groupCache[$group]);

        return $result;
    }

    /**
     * Delete option from a named group.
     *
     * @param string $group Group name.
     * @param string $key   Option key.
     * @return bool Success.
     */
    public static function deleteGroup(string $group, string $key): bool
    {
        $settingName = "wp_statistics_{$group}";

        $options = self::getGroup($group);

        if (!is_array($options) || !array_key_exists($key, $options)) {
            return false;
        }

        unset($options[$key]);

        $result = update_option($settingName, $options);

        // Invalidate cache
        unset(self::$groupCache[$group]);

        return $result;
    }

    // =========================================================================
    // Addon Options
    // =========================================================================

    /**
     * Get addon option.
     *
     * @param string      $addon   Addon name.
     * @param string|null $key     Option key (null for all addon options).
     * @param mixed       $default Default value.
     * @return mixed Option value or all addon options.
     */
    public static function getAddon(string $addon, ?string $key = null, $default = null)
    {
        $settingName = "wpstatistics_{$addon}_settings";

        if (!isset(self::$addonCache[$addon])) {
            $options = get_option($settingName);
            self::$addonCache[$addon] = is_array($options) ? $options : [];
        }

        $options = self::$addonCache[$addon];

        if ($key === null) {
            return $options ?: false;
        }

        if (!array_key_exists($key, $options)) {
            return $default ?? false;
        }

        return apply_filters("wp_statistics_option_{$settingName}_{$key}", $options[$key]);
    }

    /**
     * Set addon option.
     *
     * @param string $addon Addon name.
     * @param string $key   Option key.
     * @param mixed  $value Option value.
     * @return bool Success.
     */
    public static function setAddon(string $addon, string $key, $value): bool
    {
        $settingName = "wpstatistics_{$addon}_settings";

        $options       = self::getAddon($addon);
        $options       = is_array($options) ? $options : [];
        $options[$key] = $value;

        $result = update_option($settingName, $options);

        // Invalidate cache
        unset(self::$addonCache[$addon]);

        return $result;
    }

    /**
     * Save all addon options.
     *
     * @param string $addon   Addon name.
     * @param array  $options Options to save.
     * @return bool Success.
     */
    public static function saveAddon(string $addon, array $options): bool
    {
        $settingName = "wpstatistics_{$addon}_settings";

        $result = update_option($settingName, $options);

        // Invalidate cache
        unset(self::$addonCache[$addon]);

        return $result;
    }

    /**
     * Delete addon option.
     *
     * @param string $addon Addon name.
     * @param string $key   Option key.
     * @return bool Success.
     */
    public static function deleteAddon(string $addon, string $key): bool
    {
        $settingName = "wpstatistics_{$addon}_settings";

        $options = self::getAddon($addon);

        if (!is_array($options) || !array_key_exists($key, $options)) {
            return false;
        }

        unset($options[$key]);

        $result = update_option($settingName, $options);

        // Invalidate cache
        unset(self::$addonCache[$addon]);

        return $result;
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Get email notification address.
     *
     * @return string Email address.
     */
    public static function getEmailNotification(): string
    {
        $email = self::get('email_list', '');

        if (empty($email)) {
            $email = get_bloginfo('admin_email');
            self::set('email_list', $email);
        }

        return $email;
    }

    /**
     * Check if an option meets requirements.
     *
     * @param array  $item         Item with requirements.
     * @param string $conditionKey Key containing requirements.
     * @return bool Whether requirements are met.
     */
    public static function checkRequirements(array $item, string $conditionKey = 'require'): bool
    {
        if (!array_key_exists($conditionKey, $item)) {
            return true;
        }

        foreach ($item[$conditionKey] as $optionKey => $expectedValue) {
            $actualValue = self::get($optionKey);

            if ($expectedValue === true && !$actualValue) {
                return false;
            }

            if ($expectedValue === false && $actualValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Clear all option caches.
     *
     * Useful when switching sites in multisite or after bulk updates.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$optionsCache    = null;
        self::$userOptionsCache = [];
        self::$groupCache      = [];
        self::$addonCache      = [];
    }
}
