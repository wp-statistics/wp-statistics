<?php

namespace WP_Statistics\Service\Assets\Handlers;

use WP_Statistics\Abstracts\BaseAssets;
use WP_Statistics\Utils\Route;

/**
 * React Assets Service
 *
 * Handles WordPress admin React assets (CSS/JS) in WP Statistics plugin.
 * Manages loading and enqueuing of React-specific styles and scripts.
 *
 * @package WP_STATISTICS\Service\Assets
 * @since   15.0.0
 */
class ReactHandler extends BaseAssets
{
    /**
     * Vite dev server URL
     *
     * Can be overridden via WP_STATISTICS_VITE_DEV_SERVER constant in wp-config.php
     *
     * @var string
     */
    private const VITE_DEV_SERVER_DEFAULT = 'http://localhost:5173';

    /**
     * Manifest main JS file path
     *
     * @var string
     */
    private $manifestMainJs = '';

    /**
     * Manifest main CSS file paths
     *
     * @var array
     */
    private $manifestMainCss = [];

    /**
     * Whether Vite dev mode is active
     *
     * @var bool|null
     */
    private $isDevMode = null;

    /**
     * Get Vite dev server URL
     *
     * Can be customized via WP_STATISTICS_VITE_DEV_SERVER constant.
     *
     * @return string
     */
    private function getDevServerUrl(): string
    {
        if (defined('WP_STATISTICS_VITE_DEV_SERVER')) {
            return WP_STATISTICS_VITE_DEV_SERVER;
        }
        return self::VITE_DEV_SERVER_DEFAULT;
    }

    /**
     * Initialize the React assets manager
     *
     * @return void
     */
    public function __construct()
    {
        $this->setContext('react');
        $this->setAssetDir('public/react');

        add_action('admin_enqueue_scripts', [$this, 'styles'], 10);
        add_action('admin_enqueue_scripts', [$this, 'scripts'], 10);
    }

    /**
     * Check if Vite dev mode is active
     *
     * Dev mode is auto-enabled when the Vite dev server is running.
     * Can be explicitly disabled by setting WP_STATISTICS_VITE_DEV to false.
     *
     * @return bool
     */
    private function isDevMode(): bool
    {
        if ($this->isDevMode !== null) {
            return $this->isDevMode;
        }

        // Allow explicit disable via constant
        if (defined('WP_STATISTICS_VITE_DEV') && !WP_STATISTICS_VITE_DEV) {
            $this->isDevMode = false;
            return false;
        }

        // Auto-detect: Check if Vite dev server is running
        $this->isDevMode = $this->isViteServerRunning();
        return $this->isDevMode;
    }

    /**
     * Check if Vite dev server is running
     *
     * @return bool
     */
    private function isViteServerRunning(): bool
    {
        $response = wp_remote_get($this->getDevServerUrl() . '/@vite/client', [
            'timeout'   => 1,
            'sslverify' => false,
        ]);

        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }

    /**
     * Get allowed screen IDs for React assets.
     *
     * Add-ons can extend this list using the 'wp_statistics_react_screen_ids' filter
     * to load React assets on their own admin pages.
     *
     * @return array
     */
    private function getAllowedScreenIds(): array
    {
        $screenIds = [
            'toplevel_page_wp-statistics', // Single React SPA entry point
        ];

        /**
         * Filter the screen IDs where React assets should be loaded.
         *
         * Add-ons can use this filter to extend React functionality to their own pages.
         *
         * @since 15.0.0
         *
         * @param array $screenIds Array of WordPress admin screen IDs.
         */
        return apply_filters('wp_statistics_react_screen_ids', $screenIds);
    }

    /**
     * Register and enqueue React admin styles
     *
     * @return void
     */
    public function styles()
    {
        // Get Current Screen ID
        $screenId = Route::getScreenId();

        if (!in_array($screenId, $this->getAllowedScreenIds(), true)) {
            return;
        }

        // In dev mode, Vite injects CSS via JS - no need to enqueue separately
        if ($this->isDevMode()) {
            return;
        }

        $this->loadManifest();

        if (empty($this->manifestMainCss)) {
            return;
        }

        foreach ($this->manifestMainCss as $index => $cssFile) {
            wp_enqueue_style($this->getAssetHandle() . '-' . $index, $this->getUrl($cssFile), [], $this->getVersion());
        }
    }

    /**
     * Register and enqueue React admin scripts
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function scripts($hook = '')
    {
        // Get Current Screen ID
        $screenId = Route::getScreenId();

        if (!in_array($screenId, $this->getAllowedScreenIds(), true)) {
            return;
        }

        remove_all_actions('admin_notices');

        // Dev mode: load from Vite dev server with HMR
        if ($this->isDevMode()) {
            $this->enqueueDevScripts($hook);
            return;
        }

        // Production mode: load from built manifest
        $this->loadManifest();

        if (empty($this->manifestMainJs)) {
            return;
        }

        wp_enqueue_script_module($this->getAssetHandle(), $this->getUrl($this->manifestMainJs), [], null);
        $this->printLocalizedData($hook);
    }

    /**
     * Enqueue scripts from Vite dev server
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    private function enqueueDevScripts($hook)
    {
        $this->printLocalizedData($hook);

        $devServer = $this->getDevServerUrl();

        // All Vite scripts must load in <head> for React Fast Refresh to work
        add_action('admin_head', function () use ($devServer) {

            // 1. React Refresh preamble - must be first
            echo '<script type="module">
import RefreshRuntime from "' . esc_url($devServer) . '/@react-refresh"
RefreshRuntime.injectIntoGlobalHook(window)
window.$RefreshReg$ = () => {}
window.$RefreshSig$ = () => (type) => type
window.__vite_plugin_react_preamble_installed__ = true
</script>' . "\n";

            // 2. Vite client for HMR
            echo '<script type="module" src="' . esc_url($devServer . '/@vite/client') . '"></script>' . "\n";

            // 3. Main React entry point
            echo '<script type="module" src="' . esc_url($devServer . '/src/main.tsx') . '"></script>' . "\n";
        });
    }

    /**
     * Print localized data for React
     *
     * Since wp_localize_script doesn't work with wp_enqueue_script_module,
     * we need to print the data directly to window object before the module loads.
     *
     * @param string $hook Current admin page hook
     *
     * @return void
     */
    public function printLocalizedData($hook)
    {
        $l10n = $this->getLocalizedData($hook);

        if (is_array($l10n)) {
            foreach ($l10n as $key => $value) {
                if (!is_scalar($value)) {
                    continue;
                }

                $l10n[$key] = html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8');
            }
        }

        $script = sprintf('var wps_react = %s;', wp_json_encode($l10n));

        wp_print_inline_script_tag($script);
    }

    /**
     * Get localized data for React JavaScript
     *
     * @param string $hook Current admin page hook
     * @return array Localized data for JavaScript
     */
    protected function getLocalizedData($hook)
    {
        $list = [];

        return apply_filters('wp_statistics_react_localized_data', $list);
    }

    /**
     * Load Vite manifest file to get built asset paths.
     *
     * Reads the .vite/manifest.json file generated by Vite build process
     * to determine the correct paths for the main JS and CSS files.
     * This ensures proper asset loading for React components in production.
     *
     * @return void
     */
    private function loadManifest()
    {
        if (!empty($this->manifestMainJs) && !empty($this->manifestMainCss)) {
            return;
        }

        $manifestPath = $this->getUrl('.vite/manifest.json', true);

        if (empty($manifestPath) || !file_exists($manifestPath)) {
            return;
        }

        $manifestContent = file_get_contents($manifestPath);
        $decodedContent  = json_decode($manifestContent, true);

        if (empty($decodedContent['src/main.tsx'])) {
            return;
        }

        $this->manifestMainJs  = $decodedContent['src/main.tsx']['file'] ?? '';
        $this->manifestMainCss = $decodedContent['src/main.tsx']['css'] ?? [];
    }
}
