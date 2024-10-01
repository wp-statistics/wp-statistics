<?php

namespace WP_Statistics\Service\Analytics\DeviceDetection;

class DeviceHelper
{
    /**
     * Get all browser list for detection.
     *
     * @param bool|string $all
     * @return array|string
     */
    public static function getBrowserList($all = true)
    {
        $list = [
            'chrome'           => __('Chrome', 'wp-statistics'),
            'firefox'          => __('Firefox', 'wp-statistics'),
            'msie'             => __('Internet Explorer', 'wp-statistics'),
            'edge'             => __('Edge', 'wp-statistics'),
            'opera'            => __('Opera', 'wp-statistics'),
            'safari'           => __('Safari', 'wp-statistics'),
            'samsung_internet' => __('Samsung Internet', 'wp-statistics'),
            'firefox_mobile'   => __('Firefox Mobile', 'wp-statistics'),
            'opera_mobile'     => __('Opera Mobile', 'wp-statistics'),
            'yandex_browser'   => __('Yandex Browser', 'wp-statistics'),
            'yandex'           => __('Yandex', 'wp-statistics'),
            'uc_browser'       => __('UC Browser', 'wp-statistics'),
            'whale_browser'    => __('Whale Browser', 'wp-statistics'),
            'aloha'            => __('Aloha Browser', 'wp-statistics')
        ];

        if ($all === true) {
            return $list;
        } elseif ($all === 'key') {
            return array_keys($list);
        }

        return $list[strtolower($all)] ?? __('Unknown', 'wp-statistics');
    }

    /**
     * Returns browser logo URL.
     *
     * @param string $browser
     * @return string
     */
    public static function getBrowserLogo(string $browser)
    {
        $browser  = str_replace(' ', '_', sanitize_key(str_replace('msie', 'internet_explorer', $browser)));
        $logoPath = "assets/images/browser/{$browser}.svg";

        if (file_exists(WP_STATISTICS_DIR . $logoPath)) {
            return esc_url(WP_STATISTICS_URL . $logoPath);
        }

        return esc_url(WP_STATISTICS_URL . 'assets/images/browser/unknown.svg');
    }

    /**
     * Returns platform/OS logo URL.
     *
     * @param string $platform
     * @return string
     */
    public static function getPlatformLogo(string $platform)
    {
        $platform = str_replace(' ', '_', sanitize_text_field(strtolower($platform)));
        $logoPath = "assets/images/operating-system/{$platform}.svg";

        if (file_exists(WP_STATISTICS_DIR . $logoPath)) {
            return esc_url(WP_STATISTICS_URL . $logoPath);
        }

        return esc_url(WP_STATISTICS_URL . 'assets/images/operating-system/unknown.svg');
    }

    /**
     * Get list of platforms.
     *
     * @return array
     */
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
