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
            'aloha_browser'     => __('Aloha Browser', 'wp-statistics'),
            'brave'             => __('Brave', 'wp-statistics'),
            'chrome'            => __('Chrome', 'wp-statistics'),
            'duckduckgo'        => __('DuckDuckGo', 'wp-statistics'),
            'microsoft_edge'    => __('Edge', 'wp-statistics'),
            'firefox'           => __('Firefox', 'wp-statistics'),
            'internet_explorer' => __('Internet Explorer', 'wp-statistics'),
            'opera'             => __('Opera', 'wp-statistics'),
            'safari'            => __('Safari', 'wp-statistics'),
            'samsung_browser'   => __('Samsung Browser', 'wp-statistics'),
            'uc_browser'        => __('UC Browser', 'wp-statistics'),
            'waterfox'          => __('Waterfox', 'wp-statistics'),
            'yandex_browser'    => __('Yandex Browser', 'wp-statistics'),
            'whale_browser'     => __('Whale Browser', 'wp-statistics')
        ];

        if ($all === true) {
            return $list;
        } elseif ($all === 'key') {
            return array_keys($list);
        }

        $browser = strtolower(str_replace(' ', '_', $all));

        return $list[$browser] ?? __('Unknown', 'wp-statistics');
    }

    /**
     * Returns browser logo URL.
     *
     * @param string $browser
     * @return string
     */
    public static function getBrowserLogo(string $browser)
    {
        $browser  = str_replace(' ', '_', strtolower($browser));
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
        $platform = str_replace([' ', '/'], '_', sanitize_text_field(strtolower($platform)));
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
            'windows' => esc_html__('Windows', 'wp-statistics'),
            'mac' => esc_html__('Mac', 'wp-statistics'),
            'android' => esc_html__('Android', 'wp-statistics'),
            'ios' => esc_html__('IOS', 'wp-statistics'),
            'linux' =>  esc_html__('Linux', 'wp-statistics'),
            'ubuntu' => esc_html__('Ubuntu', 'wp-statistics'),
            'chrome_os' => esc_html__('Chrome OS', 'wp-statistics'),
            'harmony_os' => esc_html__('Harmony OS', 'wp-statistics')
        ];
    }
}
