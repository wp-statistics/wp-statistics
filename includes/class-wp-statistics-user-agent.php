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

            if ((isset($result->browser->version->value))) {
                $version = Helper::makeAnonymousVersion($result->browser->version->value);
            } else {
                $version = 'Unknown';
            }

            $agent = array(
                'browser'            => (isset($result->browser->name)) ? $result->browser->name : 'Unknown',
                'platform'           => (isset($result->os->name)) ? $result->os->name : 'Unknown',
                'version'            => $version,
                'device'             => isset($result->device->type) ? $result->getType() : 'Unknown',
                'model'              => isset($result->device->manufacturer) ? $result->device->getModel() : 'Unknown',
                'isBrowserDetected'  => isset($result->browser->name) ? true : false,
                'isPlatformDetected' => isset($result->os->name) ? true : false,
                'isBot'              => $result->isType('bot')
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
            "chrome"           => __("Chrome", 'wp-statistics'),
            "firefox"          => __("Firefox", 'wp-statistics'),
            "msie"             => __("Internet Explorer", 'wp-statistics'),
            "edge"             => __("Edge", 'wp-statistics'),
            "opera"            => __("Opera", 'wp-statistics'),
            "safari"           => __("Safari", 'wp-statistics'),
            "samsung_internet" => __("Samsung Internet", 'wp-statistics'),
            "firefox_mobile"   => __("Firefox Mobile", 'wp-statistics'),
            "opera_mobile"     => __("Opera Mobile", 'wp-statistics'),
            "yandex_browser"   => __("Yandex Browser", 'wp-statistics'),
            "yandex"           => __("Yandex", 'wp-statistics'),
            "uc_browser"       => __("UC Browser", 'wp-statistics'),
            "whale_browser"    => __("Whale Browser", 'wp-statistics'),
            "aloha"            => __("Aloha Browser", 'wp-statistics')
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
     * Returns browser logo.
     *
     * @param string $browser Browser name.
     *
     * @return  string              Logo URL, or URL of an unknown browser icon.
     */
    public static function getBrowserLogo($browser)
    {
        $browser  = str_replace(' ', '_', $browser);
        $browser  = sanitize_key($browser);
        $browser  = str_replace('msie', 'internet_explorer', $browser);
        $logoPath = "assets/images/browser/$browser.svg";

        if (file_exists(WP_STATISTICS_DIR . $logoPath)) {
            return esc_url(WP_STATISTICS_URL . $logoPath);
        }

        return esc_url(WP_STATISTICS_URL . 'assets/images/browser/unknown.svg');

    }

    public static function getBrowserInfo($userAgent = null)
    {
        $version            = '';
        $model              = _x('Unknown', 'Device Model', 'wp-statistics');
        $isBrowserDetected  = true;
        $isPlatformDetected = true;

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
            $platform           = _x('Unknown', 'Operating System', 'wp-statistics');
            $isPlatformDetected = false;
        }

        if (preg_match('/MSIE\/([0-9.]*)/i', $userAgent, $match) && !preg_match('/Opera/i', $userAgent)) {
            $browser = 'Internet Explorer';
            $version = Helper::makeAnonymousVersion(end($match));
        } elseif (preg_match('/Edg\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Edge';
            $version = Helper::makeAnonymousVersion(end($match));
        } elseif (preg_match('/Firefox\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Firefox';
            $version = Helper::makeAnonymousVersion(end($match));
        } elseif (preg_match('/OPR\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Opera';
            $version = Helper::makeAnonymousVersion(end($match));
        } elseif (preg_match('/Chromium\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Chromium';
            $version = Helper::makeAnonymousVersion(end($match));
        } elseif (preg_match('/Chrome\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Chrome';
            $version = Helper::makeAnonymousVersion(end($match));
        } elseif (preg_match('/Safari\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Safari';
            $version = Helper::makeAnonymousVersion(end($match));
        } elseif (preg_match('/Netscape[0-9]?\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Netscape';
            $version = Helper::makeAnonymousVersion(end($match));
        } elseif (preg_match('/Trident\/([0-9.]*)/i', $userAgent, $match)) {
            $browser = 'Internet Explorer';
        } else {
            $browser           = _x('Unknown', 'Browser', 'wp-statistics');
            $isBrowserDetected = false;
        }

        $pattern = '#(?<browser>)[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $userAgent, $matches)) {
            $version = _x('Unknown', 'Version', 'wp-statistics');
        }

        if (empty($version) && !empty($matches['version']) && count($matches['version'])) {
            $version = Helper::makeAnonymousVersion((end($matches['version'])));
        }

        if (preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $userAgent)) {
            $device = 'mobile';
        } else {
            $device = 'desktop';
        }

        return array(
            'browser'            => $browser,
            'version'            => $version,
            'platform'           => $platform,
            'device'             => $device,
            'model'              => $model,
            'isBrowserDetected'  => $isBrowserDetected,
            'isPlatformDetected' => $isPlatformDetected
        );
    }

    /**
     * Returns platform/OS logo.
     *
     * @param string $platform Platform name.
     *
     * @return  string              Logo URL, or URL of an unknown browser icon.
     */
    public static function getPlatformLogo($platform)
    {
        $platform = str_replace(' ', '_', $platform);
        $platform = sanitize_key($platform);
        $logoPath = "assets/images/operating-system/$platform.svg";

        if (file_exists(WP_STATISTICS_DIR . $logoPath)) {
            return esc_url(WP_STATISTICS_URL . $logoPath);
        }

        return esc_url(WP_STATISTICS_URL . 'assets/images/operating-system/unknown.svg');
    }

    public static function getPlatformsList()
    {
        return [
            'Windows',
            'OS X',
            'Android',
            'iOS',
            'Linux',
            'Ubuntu',
            'Chrome OS',
            'Harmony OS'
        ];
    }
}
