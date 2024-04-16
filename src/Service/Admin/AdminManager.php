<?php

namespace WP_Statistics\Service\Admin;

class AdminManager
{
    public function __construct()
    {
        add_filter('admin_footer_text', array($this, 'modifyAdminFooterText'), 999);
        add_filter('update_footer', array($this, 'modifyAdminUpdateFooter'), 999);
    }

    /**
     * Include footer
     */
    public function modifyAdminFooterText($text)
    {
        $screen = get_current_screen();

        if (stristr($screen->id, 'wps_')) {
            $text = sprintf(
                __('Please rate <strong>WP Statistics</strong> <a href="%2$s" title="%3$s" target="_blank">★★★★★</a> on <a href="%2$s" target="_blank">WordPress.org</a> to help us spread the word. Thank you!', 'wp-statistics'),
                esc_html__('WP Statistics', 'wp-statistics'),
                'https://wordpress.org/support/plugin/wp-statistics/reviews/?filter=5#new-post',
                esc_html__('Rate WP Statistics', 'wp-statistics')
            );
        }
        return $text;
    }

    public function modifyAdminUpdateFooter($content)
    {
        $screen = get_current_screen();

        if (stristr($screen->id, 'wps_')) {
            global $wp_version;

            $content = sprintf('<p id="footer-upgrade" class="alignright">%s | %s %s</p>',
                esc_html__('WordPress', 'wp-statistics') . ' ' . esc_html($wp_version),
                esc_html('WP Statistics'),
                esc_attr(WP_STATISTICS_VERSION)
            );
        }
        return $content;
    }
}