<?php

namespace WP_Statistics\Service;

use WP_STATISTICS\Menus;
use WP_Statistics\Components\AssetNameObfuscator;

class HooksManager
{
    public function __construct()
    {
        add_filter('kses_allowed_protocols', [$this, 'updateAllowedProtocols']);
        add_filter('plugin_action_links_' . plugin_basename(WP_STATISTICS_MAIN_FILE), [$this, 'addActionLinks']);
        add_filter('template_redirect', [$this, 'serveObfuscatedAsset']);
    }

    /**
     * Adds custom links to the plugin action links in the WordPress admin plugins page.
     *
     * @param array $links The existing plugin action links.
     *
     * @return array The modified links with the custom links added.
     */
    public function addActionLinks($links)
    {
        $customLinks = [
            '<a class="wps-premium-link-btn" target="_blank" href="https://wp-statistics.com/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugins">' . esc_html__('Get Premium', 'wp-statistics') . '</a>',
            '<a href="' . Menus::admin_url('settings') . '">' . esc_html__('Settings', 'wp-statistics') . '</a>',
            '<a target="_blank" href="https://wp-statistics.com/documentation/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugins">' . esc_html__('Docs', 'wp-statistics') . '</a>',
        ];

        return array_merge($customLinks, $links);
    }

    /**
     * Modifies the list of allowed protocols.
     *
     * @param array $protocols The list of allowed protocols.
     */
    public function updateAllowedProtocols($defaultProtocols)
    {
        $customProtocols = [
            'android-app'
        ];

        $customProtocols = apply_filters('wp_statistics_allowed_protocols', $customProtocols);

        return array_merge($defaultProtocols, $customProtocols);
    }

    /**
     * Proxies requested asset files through PHP to serve them securely.
     *
     * @return void
     */
    public function serveObfuscatedAsset()
    {
        $assetNameObfuscator = new AssetNameObfuscator();
        $dynamicAssetKey     = $assetNameObfuscator->getDynamicAssetKey();

        if (isset($_GET[$dynamicAssetKey])) {
            $asset = sanitize_text_field($_GET[$dynamicAssetKey]);
            $assetNameObfuscator->serveAssetByHash($asset);
        }
    }
}