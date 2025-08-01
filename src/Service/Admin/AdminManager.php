<?php

namespace WP_Statistics\Service\Admin;

use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class AdminManager
{
    public function __construct()
    {
        $this->initFooterModifier();
        $this->initNoticeHandler();
        $this->initSiteHealthInfo();
        $this->initAjaxOptionUpdater();
    }

    private function initFooterModifier()
    {
        add_filter('admin_footer_text', array($this, 'modifyAdminFooterText'), 999);
        add_filter('update_footer', array($this, 'modifyAdminUpdateFooter'), 999);
    }

    private function initNoticeHandler()
    {
        add_action('admin_notices', [Notice::class, 'displayNotices']);
        add_action('admin_init', [Notice::class, 'handleDismissNotice']);
        add_action('admin_init', [Notice::class, 'handleGeneralNotices'], 11);
    }

    private function initSiteHealthInfo()
    {
        // Initialize Site Health Info and register its hooks
        $siteHealthInfo = new SiteHealthInfo();
        $siteHealthInfo->register();
    }

    /**
     *
     */
    private function initAjaxOptionUpdater()
    {
        $optionUpdater = new AjaxOptionUpdater();
        $optionUpdater->init();
    }

    /**
     * Include footer
     */
    public function modifyAdminFooterText($text)
    {
        $screen = get_current_screen();

        if (apply_filters('wp_statistics_enable_footer_text', true) && stripos($screen->id, 'wps_') !== false) {
            $text = sprintf(
                __('Please rate <strong>WP Statistics</strong> <a href="%s" aria-label="%s" title="%s" target="_blank">★★★★★ %s</a> to help us spread the word. Thank you!', 'wp-statistics'),
                'https://wordpress.org/support/plugin/wp-statistics/reviews/?filter=5#new-post',
                esc_attr__('Rate WP Statistics with five stars on WordPress.org', 'wp-statistics'),
                esc_attr__('Rate WP Statistics', 'wp-statistics'),
                esc_html__('on WordPress.org', 'wp-statistics')
            );
        }
        return $text;
    }

    public function modifyAdminUpdateFooter($content)
    {
        $screen = get_current_screen();

        if (apply_filters('wp_statistics_enable_footer_text', true) && stripos($screen->id, 'wps_') !== false) {
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