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
        $parsedUrl = wp_parse_url(sanitize_url($url));

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
}
