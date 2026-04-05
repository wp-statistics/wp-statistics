<?php

namespace WP_Statistics\Service\Admin\AccessControl;

use WP_Statistics\Components\Option;

/**
 * Access level constants and helpers for WP Statistics.
 *
 * Defines a five-tier access system based on data sensitivity:
 * - NONE: No access to statistics
 * - OWN_CONTENT: View stats only for authored posts
 * - VIEW_STATS: All aggregate reports, no individual visitor data
 * - VIEW_ALL: Full access including individual visitor details (PII)
 * - MANAGE: Full access plus settings, tools, and data management
 *
 * @since 15.1.0
 */
class AccessLevel
{
    public const NONE        = 'none';
    public const OWN_CONTENT = 'own_content';
    public const VIEW_STATS  = 'view_stats';
    public const VIEW_ALL    = 'view_all';
    public const MANAGE      = 'manage';

    /**
     * Ordered list of levels from lowest to highest.
     *
     * @var string[]
     */
    private static $hierarchy = [
        self::NONE,
        self::OWN_CONTENT,
        self::VIEW_STATS,
        self::VIEW_ALL,
        self::MANAGE,
    ];

    /**
     * Get the numeric index for a level (higher = more access).
     *
     * @param string $level Access level constant.
     * @return int Index in hierarchy, or -1 if invalid.
     */
    private static function indexOf(string $level): int
    {
        $index = array_search($level, self::$hierarchy, true);
        return $index !== false ? $index : -1;
    }

    /**
     * Compare two access levels.
     *
     * @param string $a First level.
     * @param string $b Second level.
     * @return int -1 if $a < $b, 0 if equal, 1 if $a > $b.
     */
    public static function compare(string $a, string $b): int
    {
        return self::indexOf($a) <=> self::indexOf($b);
    }

    /**
     * Check if a user level meets or exceeds the required level.
     *
     * @param string $userLevel    The user's access level.
     * @param string $requiredLevel The minimum required level.
     * @return bool
     */
    public static function isAtLeast(string $userLevel, string $requiredLevel): bool
    {
        return self::indexOf($userLevel) >= self::indexOf($requiredLevel);
    }

    /**
     * Get all valid access level values.
     *
     * @return string[]
     */
    public static function getAll(): array
    {
        return self::$hierarchy;
    }

    /**
     * Get the role → access level map from options.
     *
     * Always forces administrator to MANAGE.
     *
     * @return array<string, string> Role slug => access level.
     */
    public static function getRoleLevels(): array
    {
        $levels = Option::getValue('access_levels', []);

        if (!is_array($levels) || empty($levels)) {
            $levels = self::migrateFromLegacy();
        }

        // Administrator is always manage
        $levels['administrator'] = self::MANAGE;

        return $levels;
    }

    /**
     * Get the access level for a specific role.
     *
     * @param string $roleSlug WordPress role slug.
     * @return string Access level constant.
     */
    public static function getLevelForRole(string $roleSlug): string
    {
        if ($roleSlug === 'administrator') {
            return self::MANAGE;
        }

        $levels = self::getRoleLevels();
        return $levels[$roleSlug] ?? self::NONE;
    }

    /**
     * Find the lowest WordPress capability that has at least the given access level.
     *
     * Scans the access_levels option to find all roles with the required level,
     * then returns a capability common to the least-privileged qualifying role.
     * This is needed for WordPress add_menu_page() which requires a capability string.
     *
     * @param string $requiredLevel The minimum required access level.
     * @return string A WordPress capability string.
     */
    public static function getMinimumCapabilityForLevel(string $requiredLevel): string
    {
        $levels = self::getRoleLevels();
        global $wp_roles;

        if (!is_object($wp_roles) || !is_array($wp_roles->roles)) {
            return 'manage_options';
        }

        // Find the least-privileged role that has at least the required level
        $bestCapability = 'manage_options';
        $bestRolePriority = -1;

        // Role priority: lower = less privileged
        $rolePriority = [
            'subscriber'    => 1,
            'contributor'   => 2,
            'author'        => 3,
            'editor'        => 4,
            'administrator' => 5,
        ];

        foreach ($levels as $roleSlug => $level) {
            if (!self::isAtLeast($level, $requiredLevel)) {
                continue;
            }

            // This role qualifies. Find a representative capability.
            $priority = $rolePriority[$roleSlug] ?? 3;

            if ($bestRolePriority === -1 || $priority < $bestRolePriority) {
                $bestRolePriority = $priority;

                // Get a key capability for this role
                $capMap = [
                    'subscriber'    => 'read',
                    'contributor'   => 'edit_posts',
                    'author'        => 'publish_posts',
                    'editor'        => 'edit_others_posts',
                    'administrator' => 'manage_options',
                ];

                $bestCapability = $capMap[$roleSlug] ?? self::getFirstCapability($roleSlug, $wp_roles);
            }
        }

        return $bestCapability;
    }

    /**
     * Get the first capability of a role (fallback for custom roles).
     *
     * @param string    $roleSlug WordPress role slug.
     * @param \WP_Roles $wp_roles Roles object.
     * @return string A capability string, defaults to 'manage_options'.
     */
    private static function getFirstCapability(string $roleSlug, $wp_roles): string
    {
        if (!isset($wp_roles->roles[$roleSlug])) {
            return 'manage_options';
        }

        $capabilities = $wp_roles->roles[$roleSlug]['capabilities'] ?? [];
        foreach ($capabilities as $cap => $granted) {
            if ($granted) {
                return $cap;
            }
        }

        return 'manage_options';
    }

    /**
     * Migrate legacy read_capability/manage_capability to the new access_levels format.
     *
     * Called when access_levels is empty but legacy options may exist.
     *
     * @return array<string, string> Migrated role levels.
     */
    public static function migrateFromLegacy(): array
    {
        global $wp_roles;

        $levels = [];

        if (!is_object($wp_roles) || !is_array($wp_roles->roles)) {
            return ['administrator' => self::MANAGE];
        }

        $readCap   = Option::getValue('read_capability', 'manage_options');
        $manageCap = Option::getValue('manage_capability', 'manage_options');

        foreach (array_keys($wp_roles->roles) as $roleSlug) {
            if ($roleSlug === 'administrator') {
                $levels[$roleSlug] = self::MANAGE;
                continue;
            }

            $role = $wp_roles->get_role($roleSlug);
            if (!$role) {
                $levels[$roleSlug] = self::NONE;
                continue;
            }

            if ($role->has_cap($manageCap)) {
                $levels[$roleSlug] = self::MANAGE;
            } elseif ($role->has_cap($readCap)) {
                $levels[$roleSlug] = self::VIEW_ALL;
            } else {
                $levels[$roleSlug] = self::NONE;
            }
        }

        // Persist the migration
        Option::updateValue('access_levels', $levels);

        return $levels;
    }
}
