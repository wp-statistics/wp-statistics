<?php
namespace WP_Statistics\Service\Analytics\Referrals;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Pages;
use WP_STATISTICS\Option;
use WP_Statistics\Utils\Request;
use WP_Statistics\Utils\Url;

class Referrals
{
    /**
     * Returns the raw referrer URL from the current request.
     *
     * This function checks if the request is coming from a REST API call and if the 'referred' parameter is set.
     * If so, it returns the 'referred' parameter. Otherwise, it returns the value of the 'HTTP_REFERER' server variable.
     *
     * @return string The raw referrer URL.
     */
    public static function getRawUrl()
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';

        if (Helper::is_rest_request() && Request::has('referred')) {
            $referrer = Request::get('referred', '', 'raw');

            if (Option::get('use_cache_plugin')) {
                $referrer = base64_decode($referrer);
            }

            $referrer = urldecode($referrer);
        }

        return $referrer;
    }

    /**
     * Returns the referrer URL in a standard format.
     *
     * @param string|bool $referrer Optional referrer URL. By default it will get the referrer value of the current request.
     *
     * @return string The sanitized referrer URL.
     */
    public static function getUrl($referrer = false)
    {
        // If referrer is not provided get it from the request
        $referrer = empty($referrer) ? self::getRawUrl() : $referrer;

        // If referrer is empty, return
        if (empty($referrer)) return '';

        // If referrer is my own domain, return
        if (self::isSelfReferral($referrer)) return '';

        // Sanitize url
        $referrer = sanitize_url($referrer);

        // Get protocol
        $protocol = Url::getProtocol($referrer);

        // For http, and https protocols we only want the domain
        if (in_array($protocol, ['https', 'http'])) {
            $referrer = Url::getDomain($referrer);
        }

        return $referrer;
    }

    /**
     * Returns the source channel of the given referrer.
     *
     * @return SourceDetector
     */
    public static function getSource()
    {
        $referrerUrl = self::getRawUrl();
        $pageUrl     = Pages::get_page_uri();

        return new SourceDetector($referrerUrl, $pageUrl);
    }

    /**
     * Checks if a given referrer URL is a self-referral by comparing its domain to the current website domain.
     *
     * @param string $referrer The referrer URL to check.
     * @return bool True if the referrer's domain matches the current domain, false otherwise.
     */
    public static function isSelfReferral($referrer)
    {
        $referrer   = Url::getDomain($referrer);
        $homeUrl    = Url::getDomain(home_url());

        return $referrer === $homeUrl;
    }
}