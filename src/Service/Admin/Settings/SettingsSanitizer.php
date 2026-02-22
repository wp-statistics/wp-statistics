<?php

namespace WP_Statistics\Service\Admin\Settings;

use WP_Statistics\Service\Admin\AccessControl\AccessLevel;

/**
 * Type-aware sanitization for WP Statistics settings.
 *
 * Provides reusable sanitization logic that can be called from AJAX endpoints,
 * WP-CLI commands, or any other context that writes settings.
 *
 * @since 15.0.0
 */
class SettingsSanitizer
{
    /**
     * Core textarea keys that must preserve newlines.
     *
     * @var string[]
     */
    private static $coreTextareaKeys = [
        'exclude_ip',
        'excluded_urls',
        'excluded_countries',
        'included_countries',
        'robotlist',
        'query_params_allow_list',
        'email_list',
    ];

    /**
     * Sanitize a single setting value based on its key and type.
     *
     * @param string $key   The sanitized setting key.
     * @param mixed  $value The raw value to sanitize.
     * @return mixed The sanitized value.
     */
    public static function sanitize(string $key, $value)
    {
        // Handle access_levels specially
        if ($key === 'access_levels' && is_array($value)) {
            return self::sanitizeAccessLevels($value);
        }

        if (is_array($value)) {
            return array_map('sanitize_text_field', $value);
        }

        if ($value === 'true' || $value === true) {
            return true;
        }

        if ($value === 'false' || $value === false) {
            return false;
        }

        if (is_numeric($value)) {
            return intval($value);
        }

        if (in_array($key, self::getTextareaKeys(), true)) {
            return sanitize_textarea_field($value);
        }

        return sanitize_text_field($value);
    }

    /**
     * Sanitize and validate the access_levels setting.
     *
     * Only valid role slugs and access level values are preserved.
     * Administrator is always forced to 'manage'.
     *
     * @param array $levels Raw role => level map.
     * @return array<string, string> Sanitized role => level map.
     */
    public static function sanitizeAccessLevels(array $levels): array
    {
        global $wp_roles;

        $sanitized   = [];
        $validLevels = AccessLevel::getAll();
        $validRoles  = is_object($wp_roles) && is_array($wp_roles->roles)
            ? array_keys($wp_roles->roles)
            : ['administrator'];

        foreach ($levels as $roleSlug => $level) {
            $roleSlug = sanitize_key($roleSlug);
            $level    = sanitize_text_field($level);

            if (!in_array($roleSlug, $validRoles, true)) {
                continue;
            }

            if (!in_array($level, $validLevels, true)) {
                continue;
            }

            $sanitized[$roleSlug] = ($roleSlug === 'administrator') ? AccessLevel::MANAGE : $level;
        }

        // Ensure administrator is always present
        $sanitized['administrator'] = AccessLevel::MANAGE;

        return $sanitized;
    }

    /**
     * Get textarea setting keys that must preserve newlines.
     *
     * @return string[]
     */
    public static function getTextareaKeys(): array
    {
        return self::$coreTextareaKeys;
    }
}
