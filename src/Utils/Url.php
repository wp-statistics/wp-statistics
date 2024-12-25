<?php
namespace WP_Statistics\Utils;

class Url
{
    /**
     * Retrieves the protocol from a given URL.
     *
     * @param string $url The URL from which to extract the scheme.
     * @return string The scheme extracted from the URL, or an empty string if the URL is invalid.
     */
    public static function getProtocol($url)
    {
        $parsedUrl = wp_parse_url($url);

        return $parsedUrl['scheme'] ?? '';
    }

    /**
     * Retrieves the domain from a given URL without www.
     *
     * @param string $url The URL from which to extract the domain.
     * @param bool $protocol (Optional) Whether to include the protocol in the domain. Default is false.
     * @return string The domain extracted from the URL, or an empty string if the URL is invalid.
     */
    public static function getDomain($url, $protocol = false)
    {
        // Make url lower case
        $url = strtolower($url);

        // Sanitize url
        $url = sanitize_url($url);

        // Parse URL
        $parsedUrl = wp_parse_url($url);

        // If host is empty, return early
        if (empty($parsedUrl['host'])) return '';

        // Get domain name
        $domain = $parsedUrl['host'];

        // Remove www if present
        $domain = preg_replace('/^www\./', '', $domain);

        // Remove trailing slash
        $domain = rtrim($domain, '/');

        // Add protocol
        if ($protocol && !empty($parsedUrl['scheme'])) {
            $domain = $parsedUrl['scheme'] . '://' . $domain;
        }

        return $domain;
    }

    /**
     * Formats a given URL by removing trailing slashes and adding a protocol if missing.
     *
     * @param string $url The URL to be formatted.
     * @return string The formatted URL.
     */
    public static function formatUrl($url) {
        // Remove trailing slash
        $url = rtrim($url, '/');

        // Add https protocol if missing
        if (empty(self::getProtocol($url))) {
            $url = "https://$url";
        }

        return $url;
    }

    /**
     * Checks if a specified query parameter exists in a given URL and returns its value.
     *
     * @param string $url The URL to check.
     * @param string $param The query parameter to search for.
     * @return mixed The value of the query parameter if found, or null if not.
     */
    public static function getParam($url, $param)
    {
        // Parse URL
        $parsedUrl = wp_parse_url($url);

        // If query param is empty, return early
        if (empty($parsedUrl['query'])) return null;

        // Parse query string
        parse_str($parsedUrl['query'], $params);

        // Return the query parameter value
        return $params[$param] ?? null;
    }

    /**
     * Checks if a given URL is internal by comparing its domain to the current website domain.
     *
     * @param string $url The URL to check.
     * @return bool True if the URL's domain matches the current domain, false otherwise.
     */
    public static function isInternal($url)
    {
        $url        = Url::getDomain($url);
        $homeUrl    = Url::getDomain(home_url());

        return $url === $homeUrl;
    }

    /**
     * Clean a URL by removing the protocol and www.
     *
     * @param string $url The URL to clean.
     * @return string The cleaned URL.
     */
    public static function cleanUrl($url)
    {
        $url = trim($url); // remove whitespaces
        $url = rtrim($url, '/'); // remove trailing slash
        $url = preg_replace('/^(https?:\/\/)?(www\.)?/i', '', $url); // remove protocol and www

        return $url;
    }

    /**
     * Get relative path for any post type or taxonomy term
     *
     * @param int    $id   The post ID or term ID
     * @param string $type Post type or taxonomy name
     * @return string Relative path or empty string
     */
    public static function getPath($id, $type)
    {
        if (!$type || !$id) {
            return '';
        }

        if ($type === 'author') {
            $author = get_user_by('id', $id);

            if (!$author) {
                return '';
            }

            return wp_make_link_relative(get_author_posts_url($id));
        }

        if (taxonomy_exists($type)) {
            $term = get_term($id, $type);

            if (is_wp_error($term) || !$term) {
                return '';
            }

            return wp_make_link_relative(get_term_link($term));
        }

        if (post_type_exists($type)) {
            $post = get_post($id);

            if (is_wp_error($post) || !$post || $post->post_type !== $type) {
                return '';
            }

            return wp_make_link_relative(get_permalink($post));
        }

        return '';
    }
}
