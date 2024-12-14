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
            'direct'        => esc_html__('Direct', 'wp-statistics'),
            'search'        => esc_html__('Organic Search', 'wp-statistics'),
            'paid_search'   => esc_html__('Paid Search', 'wp-statistics'),
            'display'       => esc_html__('Display', 'wp-statistics'),
            'shopping'      => esc_html__('Organic Shopping', 'wp-statistics'),
            'paid_shopping' => esc_html__('Paid Shopping', 'wp-statistics'),
            'video'         => esc_html__('Organic Video', 'wp-statistics'),
        ];

        return apply_filters('wp_statistics_source_channels_list', $channels);
    }

    /**
     * Returns the name of a source channel based on the given key.
     *
     * @param string $key The key of the source channel.
     * @return string The name of the source channel if found, Unknown otherwise.
     */
    public static function getName($key)
    {
        $channels = self::getList();
        return $channels[$key] ?? false;
    }
}