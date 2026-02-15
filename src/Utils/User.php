<?php

namespace WP_Statistics\Utils;

use WP_Statistics\Components\Option;
use WP_Statistics\Models\UserModel;
use WP_Statistics\Service\Admin\AccessControl\AccessLevel;

/**
 * Utility class for retrieving and managing WordPress user data.
 *
 * Provides methods for checking user roles and permissions, retrieving user
 * metadata and identity, assessing authentication status, and calculating
 * registration statistics. Supports both current and specified users and
 * includes compatibility with multisite and custom capabilities.
 *
 * @package WP_Statistics\Utils
 * @since 15.0.0
 */
class User
{
    /**
     * Count registered users on the site.
     *
     * @return int Total number of users.
     */
    public static function countAll()
    {
        $result = count_users();
        return !empty($result['total_users']) ? $result['total_users'] : 0;
    }

    /**
     * Calculate user‑registration rate.
     *
     * When <code>$daysBetween</code> is <code>true</code> the method returns
     * the average <em>days between</em> registered users. Otherwise it returns
     * the average <em>registrations per day</em>. The calculation starts from
     * the earliest registered user account on the site.
     *
     * @param bool $daysBetween Optional. True for days‑between, false for
     *                          registrations‑per‑day. Default false.
     * @return float            Rounded average, or 0 when no users found.
     */
    public static function getRegisterRate(bool $daysBetween = false)
    {
        $totals    = count_users();
        $userCount = !empty($totals['total_users']) ? (int)$totals['total_users'] : 0;

        if ($userCount === 0) {
            return 0;
        }

        $query = new \WP_User_Query([
            'number'  => 1,
            'orderby' => 'registered',
            'order'   => 'ASC',
            'fields'  => ['ID'],
        ]);

        $ids = $query->get_results();
        if (empty($ids)) {
            return 0;
        }

        $firstUser = get_user_by('id', $ids[0]);
        if (empty($firstUser->user_registered)) {
            return 0;
        }

        $firstTimestamp = strtotime($firstUser->user_registered);
        $daysSpan       = max(
            1,
            (int)floor((time() - $firstTimestamp) / DAY_IN_SECONDS)
        );

        return $daysBetween
            ? round($daysSpan / $userCount, 0)
            : round($userCount / $daysSpan, 2);
    }

    /**
     * Check if a user exists by their ID.
     *
     * @param int $userId WordPress user ID.
     * @return bool True when the user exists, false otherwise.
     */
    public static function exists($userId)
    {
        $userModel = new UserModel();

        $count = $userModel->exists(['ID' => $userId]);

        return $count > 0;
    }

    /**
     * Retrieve all registered role slugs plus an anonymous fallback.
     *
     * @return string[] List of role identifiers, including 'Anonymous Users'.
     */
    public static function getRoles()
    {
        global $wp_roles;

        $rolesNames   = $wp_roles->get_names();
        $rolesNames[] = 'Anonymous Users';

        return $rolesNames;
    }

    /**
     * Determine whether the current visitor is authenticated.
     *
     * @return bool True when the visitor has an active WordPress session.
     */
    public static function isLoggedIn()
    {
        return is_user_logged_in();
    }

    /**
     * Determine if the current user has administrative privileges.
     *
     * @return bool True for super-admins (multisite) or for users with 'manage_options'.
     */
    public static function isAdmin()
    {
        if (!is_user_logged_in()) {
            return false;
        }

        return is_multisite() ? is_super_admin() : current_user_can('manage_options');
    }

    /**
     * Retrieve the WordPress user‑ID for the current request.
     *
     * Returns <code>0</code> when the visitor is not authenticated. The value
     * can be filtered via the <code>wp_statistics_user_id</code> hook to
     * support custom authentication layers or anonymisation.
     *
     * @return int User ID or 0 for guests.
     */
    public static function getId()
    {
        $userId = 0;

        if (self::isLoggedIn()) {
            $userId = get_current_user_id();
        }

        return apply_filters('wp_statistics_user_id', $userId);
    }

    /**
     * Retrieve a structured array of user data for a given user ID.
     *
     * Includes core WP_User properties, roles, capabilities, and flattened meta.
     *
     * @param int $userId Optional. User ID or 0 to use current user.
     * @return array<string,mixed> Associative array of user data, or empty array if not found.
     */
    public static function getInfo(int $userId = 0)
    {
        if ($userId === 0) {
            $userId = get_current_user_id();
        }

        if ($userId === 0) {
            return [];
        }

        $user = get_userdata($userId);
        if (!$user instanceof \WP_User) {
            return [];
        }

        $info = get_object_vars($user->data);

        $info['roles'] = $user->roles;
        $info['caps']  = $user->caps;
        $info['meta']  = array_map(
            static function ($value) {
                return is_array($value) ? reset($value) : $value;
            },
            get_user_meta($userId)
        );

        return $info;
    }

    /**
     * Get the display name for a given user.
     *
     * Falls back to first+last name, or user_login if no display_name provided.
     *
     * @param int $userId Optional. User ID or 0 to use current user.
     * @return string User’s name or an empty string for guests.
     */
    public static function getName($userId = 0)
    {
        $userInfo = self::getInfo($userId);

        if (empty($userInfo)) {
            return '';
        }

        if (!empty($userInfo['display_name'])) {
            return $userInfo['display_name'];
        }

        if (!empty($userInfo['meta']['first_name'])) {
            return trim($userInfo['meta']['first_name'] . ' ' . ($userInfo['meta']['last_name'] ?? ''));
        }

        return $userInfo['user_login'] ?? '';
    }

    /**
     * Retrieve a user meta value.
     *
     * @param string $metaKey Meta key to fetch.
     * @param bool $single Whether to return a single value.
     * @param int $userId Optional. User ID or 0 for current user.
     * @param mixed $default Optional. Default value to return if meta key is not found.
     * @return mixed          Meta value(s) or false if not found.
     */
    public static function getMeta($metaKey, $single = false, $userId = 0, $default = null)
    {
        if (empty($userId)) {
            $userId = self::getId();
        }

        $meta = get_user_meta($userId, $metaKey, $single);

        return $meta ?? $default;
    }

    /**
     * Persist a user meta value.
     *
     * @param string $metaKey Meta key to update.
     * @param mixed $metaValue Value to set.
     * @param int $userId Optional. User ID or 0 for current user.
     * @return int|false        Meta ID on success, false on failure.
     */
    public static function saveMeta($metaKey, $metaValue, $userId = 0)
    {
        if (empty($userId)) {
            $userId = self::getId();
        }

        return update_user_meta($userId, $metaKey, $metaValue);
    }

    /**
     * Determine if a given capability exists on any registered role.
     *
     * @param string $capability Capability slug to check.
     * @return string The capability if found; otherwise 'manage_options'.
     */
    public static function getExistingCapability(string $capability)
    {
        global $wp_roles;

        if (!is_object($wp_roles) || !is_array($wp_roles->roles)) {
            return 'manage_options';
        }

        foreach ($wp_roles->roles as $role) {
            $capabilities = $role['capabilities'] ?? [];

            foreach ($capabilities as $capName => $enabled) {
                if ($capability === $capName) {
                    return $capability;
                }
            }
        }

        return 'manage_options';
    }

    /**
     * Determine whether a capability requires a post ID context.
     *
     * Checks if the capability name starts with 'edit_', 'delete_', or 'read_'
     * and refers to a singular resource (not plural). Plural slugs (ending in 's')
     * do not require a post ID.
     *
     * @param string $capability Capability slug to evaluate.
     * @return bool True if the capability requires a post ID, false otherwise.
     */
    public static function requiresPostContext(string $capability)
    {
        $prefixes = ['edit_', 'delete_', 'read_'];

        foreach ($prefixes as $prefix) {
            if (strpos($capability, $prefix) === 0) {
                $slug = substr($capability, strlen($prefix));

                // Plural forms (ending in "s") do not require post ID.
                return substr($slug, -1) !== 's';
            }
        }

        return false;
    }

    /**
     * Get the current user's access level.
     *
     * Checks all user roles and returns the highest access level.
     * Admins and multisite super admins always get MANAGE.
     *
     * @return string One of the AccessLevel constants.
     */
    public static function getAccessLevel(): string
    {
        if (!self::isLoggedIn()) {
            return AccessLevel::NONE;
        }

        // Super admins and administrators always get manage
        if (self::isAdmin()) {
            return AccessLevel::MANAGE;
        }

        $user = wp_get_current_user();
        if (!$user || empty($user->roles)) {
            return AccessLevel::NONE;
        }

        // Check all user roles, take the highest level
        $highestLevel = AccessLevel::NONE;
        foreach ($user->roles as $roleSlug) {
            $roleLevel = AccessLevel::getLevelForRole($roleSlug);
            if (AccessLevel::compare($roleLevel, $highestLevel) > 0) {
                $highestLevel = $roleLevel;
            }
        }

        return $highestLevel;
    }

    /**
     * Check if the current user has at least the specified access level.
     *
     * @param string $required Required minimum level (AccessLevel constant).
     * @return bool
     */
    public static function hasAccessLevel(string $required): bool
    {
        return AccessLevel::isAtLeast(self::getAccessLevel(), $required);
    }

    /**
     * Determine whether the current user has the specified permission.
     *
     * Delegates to the new access level system while maintaining backward compatibility.
     *
     * @param string $permissionType One of 'manage', 'read', or 'both'.
     * @param bool $returnCapability When true, return the capability slug instead of a boolean.
     * @return bool|string True if the user has permission (or the capability slug when requested), false otherwise.
     */
    public static function hasAccess(string $permissionType = 'both', bool $returnCapability = false)
    {
        // Map legacy permission types to access levels
        $levelMap = [
            'manage' => AccessLevel::MANAGE,
            'read'   => AccessLevel::OWN_CONTENT,
            'both'   => AccessLevel::OWN_CONTENT,
        ];

        $requiredLevel = $levelMap[$permissionType] ?? AccessLevel::OWN_CONTENT;

        if ($returnCapability) {
            return AccessLevel::getMinimumCapabilityForLevel($requiredLevel);
        }

        return self::hasAccessLevel($requiredLevel);
    }

    /**
     * Determine whether the current user has the given capability.
     *
     * Optionally checks against a specific post context when required.
     *
     * @param string $capability Capability slug to verify.
     * @param int|null $postId Optional post ID for capabilities tied to a specific post.
     * @return bool                True when the user has the capability, false otherwise.
     */
    public static function hasCapability(string $capability, ?int $postId = null)
    {
        // Ensure user is authenticated and capability is provided.
        if (!self::isLoggedIn() || $capability === '') {
            return false;
        }

        // If this capability requires a post context, ensure a post ID was provided.
        if (self::requiresPostContext($capability) && $postId === null) {
            return false;
        }

        // Multisite context: check against the current site.
        if (is_multisite()) {
            $blogId = get_current_blog_id();
            if ($blogId > 0) {
                return current_user_can_for_site($blogId, $capability);
            }
            return false;
        }

        // Contextual post-level capability.
        if ($postId !== null) {
            return current_user_can($capability, $postId);
        }

        // General capability check.
        return current_user_can($capability);
    }

    /**
     * Retrieve the Unix timestamp of the user's last login session.
     *
     * @param int $userId Optional. WordPress user ID; defaults to the current user.
     * @return int|false  The timestamp of the most recent login, or false if not available.
     */
    public static function getLastLoginTime($userId = 0)
    {
        // Default to current user when no ID provided.
        $userId = $userId ?: get_current_user_id();
        if ($userId === 0) {
            return false;
        }

        // Fetch stored session tokens.
        $sessions = get_user_meta($userId, 'session_tokens', true);
        if (!is_array($sessions) || empty($sessions)) {
            return false;
        }

        // Identify the latest login timestamp.
        $latest = false;
        foreach ($sessions as $session) {
            if (isset($session['login']) && is_numeric($session['login'])) {
                $timestamp = (int)$session['login'];
                $latest    = $latest === false ? $timestamp : max($latest, $timestamp);
            }
        }

        return $latest;
    }
}