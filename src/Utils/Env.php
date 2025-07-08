<?php

namespace WP_Statistics\Utils;

use WP_Statistics\Utils\Url;

class Env
{
    /**
     * Check if the current environment is local development
     *
     * @return bool True if running locally, false otherwise
     */
    public static function isLocal()
    {
        // Check common local development domains
        $localDomains = array(
            'localhost',
            '127.0.0.1',
            '::1',
            'local.dev',
            'local.wp',
            '*.loc',
            '*.test',
            '*.local',
        );

        // Get current site URL and parse the host
        $siteUrl = Url::getDomain(home_url());

        // Check against local domains
        foreach ($localDomains as $domain) {
            // Handle wildcard domains
            if (strpos($domain, '*') !== false) {
                $pattern = '/^' . str_replace('\*', '.*', preg_quote($domain, '/')) . '$/';
                if (preg_match($pattern, $siteUrl)) {
                    return true;
                }
            } // Exact match
            elseif (strtolower($siteUrl) === strtolower($domain)) {
                return true;
            }
        }

        // Additional checks for common local environment indicators
        if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local') {
            return true;
        }

        if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
            return true;
        }

        // Check for common local server software
        if (isset($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'localhost') !== false) {
            return true;
        }

        return false;
    }
}