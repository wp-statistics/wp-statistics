<?php
/**
 * WP Statistics SHORTINIT Polyfills
 *
 * Lightweight replacements for WordPress functions that are NOT loaded
 * in SHORTINIT mode (l10n.php, link-template.php, http.php).
 *
 * The hit pipeline avoids translation calls (see Tracker.php),
 * but l10n functions are kept as pass-through safety nets in case
 * any indirect dependency calls them.
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

// -- l10n.php (safety net — pass through untranslated) ----------------

if (!function_exists('__')) {
    function __($text, $domain = 'default')
    {
        return $text;
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default')
    {
        return $text;
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
