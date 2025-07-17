<?php

namespace WP_Statistics\Utils;

/**
 * Utilities for handling URI operations.
 *
 * @package WP_Statistics\Utils
 * @since   15.0.0
 */
class Uri
{
    /**
     * Get the current resource URI relative to the WordPress installation
     *
     * @return string The sanitized resource URI
     */
    public static function get()
    {
        $siteUri    = self::getSiteUri();
        $homeUri    = self::getHomeUri();
        $requestUri = self::getRequestUri();

        $relativeUri   = self::removeInstallationPath($requestUri, $siteUri, $homeUri);
        $sanitizedUri  = self::sanitizeUri($relativeUri);
        $normalizedUri = self::normalizeRootUri($sanitizedUri);

        return apply_filters('wp_statistics_page_uri', $normalizedUri);
    }

    /**
     * Get the site URI path
     *
     * @return string The site URI path or empty string
     */
    private static function getSiteUri()
    {
        return wp_parse_url(site_url(), PHP_URL_PATH) ?: '';
    }

    /**
     * Get the home URI path
     *
     * @return string The home URI path or empty string
     */
    private static function getHomeUri()
    {
        return wp_parse_url(home_url(), PHP_URL_PATH) ?: '';
    }

    /**
     * Get the current request URI
     *
     * @return string The sanitized request URI
     */
    private static function getRequestUri()
    {
        return sanitize_url(wp_unslash($_SERVER["REQUEST_URI"]));
    }

    /**
     * Remove the WordPress installation path from the request URI
     *
     * We need to check which URI is longer in case one contains the other.
     * For example home_uri might be "/site/wp" and site_uri might be "/site".
     * In that case we want to check to see if the resource_uri starts with "/site/wp" before
     * we check for "/site", but in the reverse case, we need to swap the order of the check.
     *
     * @param string $requestUri The current request URI
     * @param string $siteUri The site URI path
     * @param string $homeUri The home URI path
     * @return string The URI with installation path removed
     */
    private static function removeInstallationPath($requestUri, $siteUri, $homeUri)
    {
        $siteUriLength = strlen($siteUri);
        $homeUriLength = strlen($homeUri);

        if ($siteUriLength > $homeUriLength) {
            $requestUri = self::removeUriPrefix($requestUri, $siteUri);
            $requestUri = self::removeUriPrefix($requestUri, $homeUri);
        } else {
            $requestUri = self::removeUriPrefix($requestUri, $homeUri);
            $requestUri = self::removeUriPrefix($requestUri, $siteUri);
        }

        return $requestUri;
    }

    /**
     * Remove URI prefix if it matches the beginning of the target URI
     *
     * @param string $targetUri The URI to modify
     * @param string $prefix The prefix to remove
     * @return string The URI with prefix removed if it matched
     */
    private static function removeUriPrefix($targetUri, $prefix)
    {
        $prefixLength = strlen($prefix);

        if ($prefixLength > 0 && substr($targetUri, 0, $prefixLength) === $prefix) {
            return substr($targetUri, $prefixLength);
        }

        return $targetUri;
    }

    /**
     * Sanitize the resource URI
     *
     * @param string $uri The resource URI to sanitize
     * @return string The sanitized resource URI
     */
    private static function sanitizeUri($uri)
    {
        return sanitize_url($uri);
    }

    /**
     * Normalize root URI to ensure it's represented as "/"
     *
     * @param string $uri The URI to normalize
     * @return string The normalized URI
     */
    private static function normalizeRootUri($uri)
    {
        return $uri === '' ? '/' : $uri;
    }

    /**
     * Sanitize resource URI with comprehensive WordPress-specific handling
     *
     * @param object $visitorProfile The visitor profile object
     * @return string The sanitized resource URI limited to 255 characters
     */
    public static function getByVisitor($visitorProfile)
    {
        $resourceType = $visitorProfile->getCurrentPageType();
        $resourceUri  = self::get();

        if ($resourceType['type'] === "loginpage") {
            $resourceUri = QueryParams::getFilterParams($resourceUri);
        }

        return substr($resourceUri, 0, 255);
    }
}