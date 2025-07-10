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
    public static function formatUrl($url)
    {
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
     * Get query parameters of a given URL.
     *
     * @param string $url The URL to check.
     * @param string $format The format to return the value in. Could be 'string' or 'array'.
     * @return mixed The value of the query parameter if found.
     */
    public static function getParams($url, $format = 'string')
    {
        // Parse URL
        $parsedUrl = wp_parse_url($url);

        // Get query params
        $query = $parsedUrl['query'] ?? '';

        // Parse query string
        parse_str($query, $params);

        return $format === 'string' ? $query : $params;
    }

    /**
     * Checks if a given URL is internal by comparing its domain to the current website domain.
     *
     * @param string $url The URL to check.
     * @return bool True if the URL's domain matches the current domain, false otherwise.
     */
    public static function isInternal($url)
    {
        $url     = Url::getDomain($url);
        $homeUrl = Url::getDomain(home_url());

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
     * Get relative path of urls
     *
     * @param string $url
     * @return string Relative path or empty string
     */
    public static function getPath($url)
    {
        return wp_parse_url($url, PHP_URL_PATH) ?? '';
    }

    /**
     * Returns the relative path of a given URL with respect to the site's base URL.
     *
     * @return string The relative path or the original URL if it doesn't match the site URL.
     */
    public static function getRelativePathToSiteUrl()
    {
        // Build the current URL from server variables.
        if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])) {
            $scheme = 'http';
            if (
                (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') ||
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443')
            ) {
                $scheme = 'https';
            }
            $url = esc_url_raw($scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        } else {
            return null;
        }

        $site_url = site_url();

        // If the URL exactly matches the site URL, return a single slash.
        if ($url === $site_url) {
            return '/';
        }

        // If the URL starts with the site URL, return the remaining part as the relative path.
        $siteLength = strlen($site_url);
        if (substr($url, 0, $siteLength) === $site_url) {
            return substr($url, $siteLength);
        }

        // Otherwise, return the constructed URL.
        return $url;
    }

    /**
     * Get decoded URL.
     *
     * @param string $value The URL to decode.
     * @return string The decoded URL.
     */
    public static function getDecodeUrl($value)
    {
        return mb_convert_encoding(urldecode($value), 'ISO-8859-1', 'UTF-8');
    }

    /**
     * Get relative path of urls.
     *
     * @param string $url
     * @return string Relative path or empty string
     */
    public static function getRelativePath($url)
    {
        $trackingParams = QueryParams::getAllowedList('array', true);

        $parts = wp_parse_url($url);

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);

            foreach ($trackingParams as $param) {
                unset($query[$param]);
            }
        }

        $path = isset($parts['path']) ? $parts['path'] : '/';

        $relativePath = $path;

        if (!empty($query)) {
            $relativePath .= '?' . http_build_query($query);
        }

        return $relativePath;
    }
}
