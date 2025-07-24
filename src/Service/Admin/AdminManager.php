<?php

namespace WP_Statistics\Service\Admin;

use WP_Statistics\Components\Menu;
use WP_Statistics\Globals\Option;
use WP_Statistics\Utils\User;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class AdminManager
{
    public function __construct()
    {
        $this->initFooterModifier();
        $this->initNoticeHandler();
        $this->initSiteHealthInfo();
        $this->initAjaxOptionUpdater();
        $this->initAdminMenu();
    }

    /**
     * Register admin menu pages.
     *
     * @return void
     * @since 15.0.0
     */
    private function initAdminMenu()
    {
        add_action('admin_menu', [$this, 'adminMenu']);
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
        add_action('admin_init', [Notice::class, 'handleGeneralNotices']);
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

    /**
     * Register admin menu pages.
     *
     * @return void
     * @since 15.0.0
     */
    public function adminMenu()
    {
        $read_cap = User::getExistingCapability(Option::getValue('read_capability', 'manage_options'));

        foreach (Menu::getMenuList() as $key => $menu) {
            $capability = $read_cap;
            $method     = 'log';
            $name       = $menu['title'];

            if (array_key_exists('cap', $menu)) {
                $capability = $menu['cap'];
            }

            if (array_key_exists('method', $menu)) {
                $method = $menu['method'];
            }

            if (array_key_exists('name', $menu)) {
                $name = $menu['name'];
            }

            $baseNamespace = '\WP_STATISTICS\\';

            $className = isset($menu['callback']) ? $menu['callback'] : $baseNamespace . $method . '_page';

            if (method_exists($className, 'view')) {
                $callback = [$className::instance(), 'view'];
            } else {
                continue;
            }

            if (array_key_exists('sub', $menu)) {
                if (array_key_exists('break', $menu)) {
                    add_submenu_page(Menu::buildPageSlug($menu['sub']), '', '', $capability, 'wps_break_menu', $callback);
                }

                if (Option::meetsRequirements($menu) === true) {
                    add_submenu_page(Menu::buildPageSlug($menu['sub']), $menu['title'], $name, $capability, Menu::buildPageSlug($menu['page_url']), $callback);
                }
            } else {
                add_menu_page($menu['title'], $name, $capability, Menu::buildPageSlug($menu['page_url']), $callback, $menu['icon']);
            }
        }
    }
}