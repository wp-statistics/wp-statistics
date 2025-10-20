<?php

namespace WP_Statistics\Service\Admin\Assets\Handlers;

use WP_Statistics\Abstracts\BaseAdminAssets;
use WP_Statistics\Utils\Route;

/**
 * React Assets Service
 *
 * Handles WordPress admin React assets (CSS/JS) in WP Statistics plugin.
 * Manages loading and enqueuing of React-specific styles and scripts.
 *
 * @package WP_STATISTICS\Service\Admin\Assets
 * @since   15.0.0
 */
class ReactHandler extends BaseAdminAssets
{
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
     * Initialize the React assets manager
     *
     * @return void
     */
    public function __construct()
    {
        $this->setContext('react');
        $this->setAssetDir('frontend/dist');

        add_action('admin_enqueue_scripts', [$this, 'adminStyles'], 10);
        add_action('admin_enqueue_scripts', [$this, 'adminScripts'], 10);
    }

    /**
     * Register and enqueue React admin styles
     *
     * @return void
     */
    public function adminStyles()
    {
        // Get Current Screen ID
        $screenId = Route::getScreenId();

        if ('admin_page_wp-statistics-root' !== $screenId) {
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
    public function adminScripts($hook)
    {
        // Get Current Screen ID
        $screenId = Route::getScreenId();

        if ('admin_page_wp-statistics-root' !== $screenId) {
            return;
        }

        remove_all_actions('admin_notices');

        $this->loadManifest();

        if (empty($this->manifestMainJs)) {
            return;
        }

        wp_enqueue_script_module($this->getAssetHandle(), $this->getUrl($this->manifestMainJs), [], $this->getVersion(), true);
        $this->printLocalizedData($hook);
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
                if (! is_scalar($value)) {
                    continue;
                }

                $l10n[$key] = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
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
