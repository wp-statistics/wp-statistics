<?php

namespace WP_STATISTICS;

class UserAgent
{
    /**
     * Get User Agent
     *
     * @return mixed
     */
    public static function getHttpUserAgent()
    {
        return apply_filters('wp_statistics_user_http_agent', (isset($_SERVER['HTTP_USER_AGENT']) ? wp_unslash($_SERVER['HTTP_USER_AGENT']) : ''));
    }

    /**
     * Calls the user agent parsing code.
     *
     * @return array|\string[]
     */
    public static function getUserAgent()
    {

        // Get Http User Agent
        $user_agent = self::getHttpUserAgent();

        if (version_compare(phpversion(), '7', ">=") && class_exists('\WhichBrowser\Parser')) {
            // Get WhichBrowser Browser
            $result = new \WhichBrowser\Parser($user_agent);
            $agent  = array(
                'browser'  => (isset($result->browser->name)) ? $result->browser->name : _x('Unknown', 'Browser', 'wp-statistics'),
                'platform' => (isset($result->os->name)) ? $result->os->name : _x('Unknown', 'Platform', 'wp-statistics'),
                'version'  => (isset($result->browser->version->value)) ? $result->browser->version->value : _x('Unknown', 'Version', 'wp-statistics'),
                'device'   => isset($result->device->type) ? $result->getType() : _x('Unknown', 'Device', 'wp-statistics'),
                'model'    => isset($result->device->manufacturer) ? $result->device->getModel() : _x('Unknown', 'Model', 'wp-statistics'),
            );
        } else {
            $agent = self::getBrowserInfo($user_agent);
        }

        return apply_filters('wp_statistics_user_agent', $agent);
    }

    /**
     * Get All Browser List For Detecting
     *
     * @param bool $all
     * @area utility
     * @return array|mixed
     */
    public static function BrowserList($all = true)
    {

        //List Of Detect Browser in WP Statistics
        $list        = array(
            "chrome"  => __("Chrome", 'wp-statistics'),
            "firefox" => __("Firefox", 'wp-statistics'),
            "msie"    => __("Internet Explorer", 'wp-statistics'),
            "edge"    => __("Edge", 'wp-statistics'),
            "opera"   => __("Opera", 'wp-statistics'),
            "safari"  => __("Safari", 'wp-statistics')
        );
        $browser_key = array_keys($list);

        //Return All Browser List
        if ($all === true) {
            return $list;
            //Return Browser Keys For detect
        } elseif ($all == "key") {
            return $browser_key;
        } else {
            //Return Custom Browser Name by key
            if (array_search(strtolower($all), $browser_key) !== false) {
                return $list[strtolower($all)];
            } else {
                return __("Unknown", 'wp-statistics');
            }
        }
    }

    /**
     * Get Browser Logo
     *
     * @param $browser
     * @return string
     */
    public static function getBrowserLogo($browser)
    {
        $name = 'unknown';
        if (array_search(strtolower($browser), self::BrowserList('key')) !== false) {
            $name = $browser;
        }

        return WP_STATISTICS_URL . 'assets/images/browser/' . $name . '.png';
    }

    public static function getBrowserInfo($userAgent = null)
    {
        $version      = '';
        $model = _x('Unknown', 'Device Model', 'wp-statistics');

        if (preg_match('/linux|ubuntu/i', $userAgent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'windows';
        } elseif (preg_match('/iphone/i', $userAgent)) {
            $platform = 'iPhone';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/webos/i', $userAgent)) {
            $platform = 'Mobile';
        } else {
            $platform = _x('Unknown', 'Platform', 'wp-statistics');
        }

        if (preg_match('/MSIE\/([0-9.]*)/i', $userAgent, $match) && !preg_match('/Opera/i', $userAgent)) {
            $browser = 'Internet Explorer';
            $version = end($match);
        } elseif (preg_match('/Edg\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Edge';
            $version = end($match);
        } elseif (preg_match('/Firefox\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Firefox';
            $version = end($match);
        } elseif (preg_match('/OPR\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Opera';
            $version = end($match);
        } elseif (preg_match('/Chromium\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Chromium';
            $version = end($match);
        } elseif (preg_match('/Chrome\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Chrome';
            $version = end($match);
        } elseif (preg_match('/Safari\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Safari';
            $version = end($match);
        } elseif (preg_match('/Netscape[0-9]?\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Netscape';
            $version = end($match);
        } elseif (preg_match('/Trident\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Internet Explorer';
        } else {
            $browser = _x('Unknown', 'Browser', 'wp-statistics');
        }

        $pattern = '#(?<browser>)[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $userAgent, $matches)) {
            $version = _x('Unknown', 'Version', 'wp-statistics');
        }

        if (empty($version) && !empty($matches['version']) && count($matches['version'])) {
            $version = end($matches['version']);
        }

        if (preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo
|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $userAgent)) {
            $device = 'mobile';
        } else {
            $device = 'desktop';
        }

        return array(
            'browser'  => $browser,
            'version'  => $version,
            'platform' => $platform,
            'device'   => $device,
            'model'    => $model,
        );
    }

}