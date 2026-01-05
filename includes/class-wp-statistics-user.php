<?php

namespace WP_STATISTICS;

use WP_Statistics\Components\DateRange;
use WP_Statistics\Utils\User as UtilsUser;

/**
 * Legacy User class for backward compatibility.
 *
 * @deprecated 15.0.0 Use \WP_Statistics\Utils\User instead.
 * @see \WP_Statistics\Utils\User
 *
 * This class is maintained for backward compatibility with add-ons.
 * New code should use \WP_Statistics\Utils\User directly.
 *
 * Migration guide:
 * - User::is_login()              -> UtilsUser::isLoggedIn()
 * - User::get_user_id()           -> UtilsUser::getId()
 * - User::get()                   -> UtilsUser::getInfo()
 * - User::get_name()              -> UtilsUser::getName()
 * - User::exists()                -> UtilsUser::exists()
 * - User::getMeta()               -> UtilsUser::getMeta()
 * - User::saveMeta()              -> UtilsUser::saveMeta()
 * - User::get_role_list()         -> UtilsUser::getRoles()
 * - User::ExistCapability()       -> UtilsUser::getExistingCapability()
 * - User::Access()                -> UtilsUser::hasAccess()
 * - User::isAdmin()               -> UtilsUser::isAdmin()
 * - User::checkUserCapability()   -> UtilsUser::hasCapability()
 * - User::isCapabilityNeedingPostId() -> UtilsUser::requiresPostContext()
 * - User::getLastLogin()          -> UtilsUser::getLastLoginTime()
 */
class User
{
    /**
     * Default Manage User Capability
     *
     * @var string
     */
    public static $default_manage_cap = 'manage_options';

    public static $dateFilterMetaKey = 'wp_statistics_metabox_date_filter';

    /**
     * Check User is Logged in WordPress
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::isLoggedIn() instead.
     * @see \WP_Statistics\Utils\User::isLoggedIn()
     *
     * @return bool
     */
    public static function is_login()
    {
        return UtilsUser::isLoggedIn();
    }

    /**
     * Get Current User ID
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::getId() instead.
     * @see \WP_Statistics\Utils\User::getId()
     *
     * @return int
     */
    public static function get_user_id()
    {
        return UtilsUser::getId();
    }

    /**
     * Get User Data
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::getInfo() instead.
     * @see \WP_Statistics\Utils\User::getInfo()
     *
     * @param int|bool $user_id
     * @return array
     */
    public static function get($user_id = false)
    {
        $userId   = $user_id ? (int) $user_id : get_current_user_id();
        $userInfo = UtilsUser::getInfo($userId);

        // Maintain backward compatibility for 'role' key (legacy uses 'role', new uses 'roles')
        if (isset($userInfo['roles'])) {
            $userInfo['role'] = $userInfo['roles'];
        }

        // Maintain backward compatibility for 'cap' key (legacy uses 'cap', new uses 'caps')
        if (isset($userInfo['caps'])) {
            $userInfo['cap'] = $userInfo['caps'];
        }

        return $userInfo;
    }

    /**
     * Get user meta value.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::getMeta() instead.
     * @see \WP_Statistics\Utils\User::getMeta()
     *
     * @param string   $metaKey The meta key.
     * @param bool     $single  Whether to return a single value.
     * @param int|bool $userId  User ID or false for current user.
     * @return mixed
     */
    public static function getMeta($metaKey, $single = false, $userId = false)
    {
        return UtilsUser::getMeta($metaKey, $single, $userId ? (int) $userId : 0);
    }

    /**
     * Save user meta value.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::saveMeta() instead.
     * @see \WP_Statistics\Utils\User::saveMeta()
     *
     * @param string   $metaKey   The meta key.
     * @param mixed    $metaValue The meta value.
     * @param int|bool $userId    User ID or false for current user.
     * @return int|bool
     */
    public static function saveMeta($metaKey, $metaValue, $userId = false)
    {
        return UtilsUser::saveMeta($metaKey, $metaValue, $userId ? (int) $userId : 0);
    }

    /**
     * Get Full name of User
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::getName() instead.
     * @see \WP_Statistics\Utils\User::getName()
     *
     * @param int $user_id
     * @return string
     */
    public static function get_name($user_id)
    {
        return UtilsUser::getName((int) $user_id);
    }

    /**
     * Check User Exist By id
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::exists() instead.
     * @see \WP_Statistics\Utils\User::exists()
     *
     * @param int $user_id
     * @return bool
     */
    public static function exists($user_id)
    {
        return UtilsUser::exists((int) $user_id);
    }

    /**
     * Returns WordPress' roles names + an extra "Anonymous Users" index.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::getRoles() instead.
     * @see \WP_Statistics\Utils\User::getRoles()
     *
     * @return array
     */
    public static function get_role_list()
    {
        return UtilsUser::getRoles();
    }

    /**
     * Validation User Capability
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::getExistingCapability() instead.
     * @see \WP_Statistics\Utils\User::getExistingCapability()
     *
     * @param string $capability Capability
     * @return string
     */
    public static function ExistCapability($capability)
    {
        return UtilsUser::getExistingCapability((string) $capability);
    }

    /**
     * Check User Access To WP Statistics Admin
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::hasAccess() instead.
     * @see \WP_Statistics\Utils\User::hasAccess()
     *
     * @param string         $type   One of 'manage', 'read', or 'both'.
     * @param string|boolean $export Pass 'cap' to return capability slug instead of boolean.
     * @return bool|string
     */
    public static function Access($type = 'both', $export = false)
    {
        return UtilsUser::hasAccess($type, $export === 'cap');
    }

    /**
     * Get Date Filter
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Service\Admin\UserPreferences\UserPreferencesManager instead.
     * @see \WP_Statistics\Service\Admin\UserPreferences\UserPreferencesManager
     *
     * @param string $metaKey
     * @return array
     */
    public static function getDefaultDateFilter($metaKey)
    {
        $dateFilters = self::getMeta(self::$dateFilterMetaKey, true);

        // Return default date filter
        if (empty($dateFilters) || empty($dateFilters[$metaKey])) {
            $range = DateRange::get(DateRange::$defaultPeriod);

            return [
                'type'   => 'filter',
                'filter' => DateRange::$defaultPeriod,
                'from'   => $range['from'],
                'to'     => $range['to']
            ];
        }

        $dateFilter = $dateFilters[$metaKey];
        [$filterType, $dateFilter] = explode('|', $dateFilter);

        if ($filterType === 'custom') {
            [$from, $to] = explode(':', $dateFilter);
        } elseif ($filterType === 'filter') {
            $range = DateRange::get($dateFilter);
            $from  = $range['from'];
            $to    = $range['to'];
        }

        return [
            'type'   => $filterType,
            'filter' => $dateFilter,
            'from'   => $from,
            'to'     => $to
        ];
    }

    /**
     * Save Date Filter
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Service\Admin\UserPreferences\UserPreferencesManager instead.
     * @see \WP_Statistics\Service\Admin\UserPreferences\UserPreferencesManager
     *
     * @param string $metaKey
     * @param array  $args
     * @return void
     */
    public static function saveDefaultDateFilter($metaKey, $args)
    {
        // Return early if necessary fields are not set
        if (!isset($args['filter'], $args['from'], $args['to'])) {
            return;
        }

        // Get metaboxes date filters
        $dateFilters = self::getMeta(self::$dateFilterMetaKey, true);

        // Check if date filters is empty, use default array
        if (empty($dateFilters)) {
            $dateFilters = [];
        }

        // Get period from range
        $period = DateRange::get($args['filter']);

        // Store date in the database depending on wether the period exists or not
        if (!empty($period)) {
            $value = "filter|{$args['filter']}";
        } else {
            $value = "custom|{$args['from']}:{$args['to']}";
        }

        // Update meta value
        $dateFilters[$metaKey] = sanitize_text_field($value);
        self::saveMeta(self::$dateFilterMetaKey, $dateFilters);
    }

    /**
     * Retrieves the last login time of a WordPress user.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::getLastLoginTime() instead.
     * @see \WP_Statistics\Utils\User::getLastLoginTime()
     *
     * @param int|false $userId The ID of the user to retrieve the last login time for. Defaults to the current user.
     * @return int|false The last login time of the user, or false if no login time is found.
     */
    public static function getLastLogin($userId = false)
    {
        return UtilsUser::getLastLoginTime($userId ? (int) $userId : 0);
    }

    /**
     * Check if the current user is an administrator or super admin in multisite network.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::isAdmin() instead.
     * @see \WP_Statistics\Utils\User::isAdmin()
     *
     * @return bool Whether the current user is an administrator.
     */
    public static function isAdmin()
    {
        return UtilsUser::isAdmin();
    }

    /**
     * Check if the current user has the specified capability.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::hasCapability() instead.
     * @see \WP_Statistics\Utils\User::hasCapability()
     *
     * @param string   $capability The user capability to check.
     * @param int|null $postId     The post ID.
     * @return bool|null Whether the current user has the specified capability.
     */
    public static function checkUserCapability($capability, $postId = null)
    {
        // Maintain backward compatibility for null return on empty/invalid input
        if (!UtilsUser::isLoggedIn() || empty($capability)) {
            return null;
        }

        if (UtilsUser::requiresPostContext((string) $capability) && empty($postId)) {
            return null;
        }

        return UtilsUser::hasCapability((string) $capability, $postId) ? true : null;
    }

    /**
     * Checks if a capability requires a post ID.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Utils\User::requiresPostContext() instead.
     * @see \WP_Statistics\Utils\User::requiresPostContext()
     *
     * @param string $capability
     * @return bool
     */
    public static function isCapabilityNeedingPostId($capability)
    {
        return UtilsUser::requiresPostContext((string) $capability);
    }
}