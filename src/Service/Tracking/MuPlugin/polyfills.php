<?php
/**
 * WP Statistics SHORTINIT Polyfills
 *
 * Lightweight replacements for WordPress functions that are NOT loaded
 * in SHORTINIT mode (pluggable.php, l10n.php, link-template.php, http.php).
 *
 * Functions already available in SHORTINIT (no polyfill needed):
 * - Hooks: add_filter, apply_filters, do_action, has_filter (plugin.php)
 * - Sanitization: sanitize_text_field, esc_html, sanitize_url (formatting.php)
 * - Options: get_option, update_option (functions.php -> option.php)
 * - Utilities: wp_parse_args, absint, wp_privacy_anonymize_ip (functions.php)
 *
 * @since 15.0.0
 */

// Only define polyfills in SHORTINIT mode — these must not override core functions during normal WordPress loads.
if (!defined('SHORTINIT') || !SHORTINIT) {
    return;
}

// ── l10n.php (translations — not needed for tracking endpoint) ───────

if (!function_exists('__')) {
    function __($text, $domain = 'default')
    {
        return $text;
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default')
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

// ── pluggable.php ────────────────────────────────────────────────────

if (!function_exists('wp_salt')) {
    function wp_salt($scheme = 'auth')
    {
        return AUTH_KEY . AUTH_SALT;
    }
}

if (!function_exists('wp_generate_password')) {
    function wp_generate_password($length = 12, $special_chars = true, $extra_special_chars = false)
    {
        return substr(base64_encode(random_bytes((int) ceil($length * 0.75))), 0, $length);
    }
}

if (!function_exists('get_user_by')) {
    function get_user_by($field, $value)
    {
        if ($field !== 'id' && $field !== 'ID') {
            return false;
        }

        global $wpdb;

        $user = $wpdb->get_row(
            $wpdb->prepare("SELECT ID, user_login FROM {$wpdb->users} WHERE ID = %d", $value)
        );

        if (!$user) {
            return false;
        }

        $meta_key     = $wpdb->prefix . 'capabilities';
        $capabilities = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s",
                $user->ID,
                $meta_key
            )
        );

        $roles = [];
        if ($capabilities) {
            $caps = maybe_unserialize($capabilities);
            if (is_array($caps)) {
                $roles = array_keys(array_filter($caps));
            }
        }

        $user->roles = $roles;

        return $user;
    }
}

// ── link-template.php ────────────────────────────────────────────────

if (!function_exists('home_url')) {
    function home_url($path = '', $scheme = null)
    {
        $home = get_option('home');

        if ($path && is_string($path)) {
            return rtrim($home, '/') . '/' . ltrim($path, '/');
        }

        return $home;
    }
}

// ── http.php ─────────────────────────────────────────────────────────

if (!function_exists('wp_parse_url')) {
    function wp_parse_url($url, $component = -1)
    {
        return parse_url($url, $component);
    }
}
