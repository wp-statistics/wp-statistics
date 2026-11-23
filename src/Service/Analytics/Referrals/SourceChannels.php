<?php
namespace WP_Statistics\Service\Analytics\Referrals;

class SourceChannels
{
    /**
     * Returns the list of source channels.
     *
     * @return array List of source channels.
     */
    public static function getList()
    {
        $channels = [
            'direct'                => esc_html__('Direct Traffic', 'wp-statistics'),
            'search'                => esc_html__('Organic Search', 'wp-statistics'),
            'ai_assistants'         => esc_html__('AI Assistants', 'wp-statistics'),
            'paid_search'           => esc_html__('Paid Search', 'wp-statistics'),
            'affiliates'            => esc_html__('Affiliates', 'wp-statistics'),
            'audio'                 => esc_html__('Audio', 'wp-statistics'),
            'display'               => esc_html__('Display', 'wp-statistics'),
            'email'                 => esc_html__('Email', 'wp-statistics'),
            'mobile_notification'   => esc_html__('Mobile Notification', 'wp-statistics'),
            'shopping'              => esc_html__('Organic Shopping', 'wp-statistics'),
            'social'                => esc_html__('Organic Social', 'wp-statistics'),
            'video'                 => esc_html__('Organic Video', 'wp-statistics'),
            'paid_shopping'         => esc_html__('Paid Shopping', 'wp-statistics'),
            'paid_social'           => esc_html__('Paid Social', 'wp-statistics'),
            'paid_video'            => esc_html__('Paid Video', 'wp-statistics'),
            'paid_other'            => esc_html__('Paid Other', 'wp-statistics'),
            'referral'              => esc_html__('Referral', 'wp-statistics'),
            'sms'                   => esc_html__('SMS', 'wp-statistics'),
            'unassigned'            => esc_html__('Unassigned Traffic', 'wp-statistics')
        ];

        return apply_filters('wp_statistics_source_channels_list', $channels);
    }

    /**
     * Returns the name of a source channel based on the given key.
     *
     * An empty key (NULL or '', as found in older or imported rows) and a key that is not in
     * the list are treated as unassigned traffic, so no "Undefined array key" warning is raised.
     *
     * @param string|null $key The key of the source channel.
     * @return string The name of the source channel if found, the unassigned label otherwise.
     */
    public static function getName($key)
    {
        $channels = self::getList();

        if (is_string($key) && $key !== '' && isset($channels[$key])) {
            return $channels[$key];
        }

        return $channels['unassigned'] ?? esc_html__('Unassigned Traffic', 'wp-statistics');
    }

    /**
     * Fixed colour and icon per source channel, so a category looks the same on every site,
     * whatever its rank (Top Source Categories widget, Overview and Referrals Overview).
     *
     * Icons are the inner markup of a 24x24 stroke SVG (stroke = currentColor).
     *
     * @return array<string, array{color: string, icon: string}>
     */
    public static function getStyles()
    {
        $styles = [
            'search'              => ['color' => '#4285F4', 'icon' => '<circle cx="11" cy="11" r="6.5"/><path d="M20 20l-4.2-4.2"/>'],
            'direct'              => ['color' => '#7D8083', 'icon' => '<path d="M3 12h11M10 7l5 5-5 5M17 4h3v16h-3"/>'],
            'social'              => ['color' => '#9C27B0', 'icon' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20a6 6 0 0 1 12 0"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.2a5 5 0 0 1 5.5 5.3"/>'],
            'paid_search'         => ['color' => '#34A853', 'icon' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.6-4.6M10.5 7.5v6M12.3 8.6c-.4-.6-1.1-.9-1.9-.9-1 0-1.9.6-1.9 1.4 0 1.9 3.8 1 3.8 2.9 0 .8-.9 1.4-1.9 1.4-.8 0-1.6-.3-2-.9"/>'],
            'ai_assistants'       => ['color' => '#E8A100', 'icon' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/><path d="M18.5 15.5l.7 1.8 1.8.7-1.8.7-.7 1.8-.7-1.8-1.8-.7 1.8-.7z"/>'],
            'unassigned'          => ['color' => '#5F6B7A', 'icon' => '<circle cx="12" cy="12" r="9"/><path d="M9.6 9.3a2.5 2.5 0 0 1 4.8.9c0 1.7-2.4 2.2-2.4 3.8M12 17.2v.1"/>'],
            'paid_social'         => ['color' => '#E8457C', 'icon' => '<path d="M4 10v4h3l6 4V6L7 10z"/><path d="M16.5 9a4 4 0 0 1 0 6M19 6.5a7.5 7.5 0 0 1 0 11"/>'],
            'email'               => ['color' => '#00A3B4', 'icon' => '<rect x="3" y="5.5" width="18" height="13" rx="2"/><path d="M3.5 7l8.5 6 8.5-6"/>'],
            'video'               => ['color' => '#EA4335', 'icon' => '<rect x="3" y="5.5" width="18" height="13" rx="3"/><path d="M10 9.2v5.6l4.8-2.8z"/>'],
            'paid_video'          => ['color' => '#C62828', 'icon' => '<rect x="3" y="5.5" width="18" height="13" rx="3"/><path d="M9 9.2v5.6l4.8-2.8zM17 9.5v5"/>'],
            'paid_other'          => ['color' => '#F57C3B', 'icon' => '<path d="M3.5 12.5V4.5h8l9 9-8 8z"/><circle cx="8" cy="9" r="1.4"/>'],
            'referral'            => ['color' => '#5C6BC0', 'icon' => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>'],
            'affiliates'          => ['color' => '#795548', 'icon' => '<circle cx="7" cy="12" r="2.5"/><circle cx="17" cy="6" r="2.5"/><circle cx="17" cy="18" r="2.5"/><path d="M9.2 10.8l5.6-3.6M9.2 13.2l5.6 3.6"/>'],
            'audio'               => ['color' => '#3949AB', 'icon' => '<path d="M4 15v-3a8 8 0 0 1 16 0v3"/><rect x="3" y="14" width="4" height="6" rx="1.5"/><rect x="17" y="14" width="4" height="6" rx="1.5"/>'],
            'display'             => ['color' => '#00897B', 'icon' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>'],
            'mobile_notification' => ['color' => '#AFB42B', 'icon' => '<path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15z"/><path d="M10 20.5a2 2 0 0 0 4 0"/>'],
            'shopping'            => ['color' => '#F4511E', 'icon' => '<path d="M3 4h2.5l2.2 10.5h10.6L20.5 7H6.4"/><circle cx="9" cy="19" r="1.4"/><circle cx="17" cy="19" r="1.4"/>'],
            'paid_shopping'       => ['color' => '#BF360C', 'icon' => '<path d="M5 8h14l-1 12H6z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8M12 11v6M13.6 12.2c-.3-.4-.9-.7-1.6-.7-.9 0-1.6.5-1.6 1.2 0 1.6 3.2.9 3.2 2.5 0 .7-.7 1.2-1.6 1.2-.7 0-1.3-.3-1.6-.7"/>'],
            'sms'                 => ['color' => '#26A69A', 'icon' => '<path d="M4 5h16v11H9l-5 4z"/><path d="M8 10.5h.01M12 10.5h.01M16 10.5h.01"/>'],
        ];

        /**
         * Filters the colour and icon of each source channel.
         *
         * @param array $styles key => ['color' => '#hex', 'icon' => '<path .../>' (24x24, stroke)].
         */
        return apply_filters('wp_statistics_source_channel_styles', $styles);
    }

    /**
     * Colour and icon of one source channel. Empty and unknown keys get the unassigned style.
     *
     * @param string|null $key
     * @return array{color: string, icon: string}
     */
    public static function getStyle($key)
    {
        $styles   = self::getStyles();
        $fallback = ['color' => '#5F6B7A', 'icon' => '<circle cx="12" cy="12" r="9"/><path d="M9.6 9.3a2.5 2.5 0 0 1 4.8.9c0 1.7-2.4 2.2-2.4 3.8M12 17.2v.1"/>'];

        if (is_string($key) && $key !== '' && isset($styles[$key])) {
            return array_merge($fallback, $styles[$key]);
        }

        return array_merge($fallback, $styles['unassigned'] ?? []);
    }
}