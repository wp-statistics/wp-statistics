<?php

namespace WP_Statistics\Service;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;

class HooksManager
{
    public function __construct()
    {
        add_filter('kses_allowed_protocols', [$this, 'updateAllowedProtocols']);
        add_filter('plugin_action_links_' . plugin_basename(WP_STATISTICS_MAIN_FILE), [$this, 'addActionLinks']);
        add_filter('template_redirect', [$this, 'proxyFile']);
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
    public function proxyFile()
    {
        if (isset($_GET['assets'])) {
            $asset             = sanitize_text_field($_GET['assets']);
            $hashedAssetsArray = Option::getOptionGroup('hashed_assets', null, []);
            $originalFilePath  = $this->getOriginalFilePath($asset, $hashedAssetsArray);

            if ($originalFilePath && file_exists($originalFilePath)) {
                header('Content-Type: application/javascript');
                header('Cache-Control: public, max-age=86400');

                readfile($originalFilePath);

                exit();
            } else {
                wp_die(__('File not found.', 'wp-statistics'), __('404 Not Found', 'wp-statistics'), array('response' => 404));
            }
        }
    }

    /**
     * Retrieves the original file path based on a hashed file name.
     *
     * @param string $hashedFileName
     *
     * @param array $hashedAssetsArray
     *
     * @return string|null
     */
    private function getOriginalFilePath($hashedFileName, $hashedAssetsArray)
    {
        if (!empty($hashedAssetsArray)) {
            foreach ($hashedAssetsArray as $originalPath => $info) {
                if (isset($info['dir']) && basename($info['dir']) === $hashedFileName) {
                    return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $originalPath;
                }
            }
        }

        return null;
    }
}