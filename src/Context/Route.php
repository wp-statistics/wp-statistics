<?php
namespace WP_Statistics\Context;

/**
 * WordPress route‑detection helper.
 *
 * Provides static methods that determine which core WordPress
 * screen or endpoint the current HTTP request is targeting.
 * Currently exposes {@see Route::isLoginPage()} but can be
 * extended with additional route checks (REST, admin‑ajax, etc.).
 *
 * @package WP_Statistics\Context
 * @since   15.0.0
 */
final class Route
{
    /**
     * Returns true when the current request is for the core WordPress
     * login or registration screen (wp-login.php / wp-register.php).
     *
     * @return bool
     */
    public static function isLoginPage()
    {
        if (isset($GLOBALS['pagenow']) &&
            in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php'], true)) {
            return true;
        }

        if (defined('WP_CLI') && WP_CLI) {
            return false;
        }

        if (empty($_SERVER['HTTP_HOST']) || empty($_SERVER['SCRIPT_NAME'])) {
            return false;
        }

        $scheme      = is_ssl() ? 'https' : 'http';
        $host        = sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']));
        $script      = sanitize_text_field(wp_unslash($_SERVER['SCRIPT_NAME']));
        $currentUrl  = $scheme . '://' . $host . $script;

        $currentPath = wp_parse_url($currentUrl,  PHP_URL_PATH);
        $loginPath   = wp_parse_url(wp_login_url(), PHP_URL_PATH);

        return $currentPath === $loginPath;
    }
}