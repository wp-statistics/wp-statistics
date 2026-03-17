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

// Only define polyfills when loaded by the WP Statistics SHORTINIT endpoint.
// These lightweight stubs must never override core WordPress functions during normal requests.
if (!defined('WP_STATISTICS_SHORTINIT') || !WP_STATISTICS_SHORTINIT) {
    return;
}

// -- l10n.php (translations - not needed for tracking endpoint) -------

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

// -- pluggable.php ----------------------------------------------------

if (!function_exists('wp_generate_password')) {
    function wp_generate_password($length = 12, $special_chars = true, $extra_special_chars = false)
    {
        return substr(base64_encode(random_bytes((int) ceil($length * 0.75))), 0, $length);
    }
}

// -- link-template.php ------------------------------------------------

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

// -- http.php ---------------------------------------------------------

if (!function_exists('wp_parse_url')) {
    function wp_parse_url($url, $component = -1)
    {
        return parse_url($url, $component);
    }
}
