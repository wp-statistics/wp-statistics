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
     * Initialize the React assets manager
     *
     * @return void
     */
    public function __construct()
    {
        $this->setContext('react');
        $this->setAssetDir('assets/dist/react');

        add_action('admin_enqueue_scripts', [$this, 'adminStyles'], 999);
        add_action('admin_enqueue_scripts', [$this, 'adminScripts'], 999);
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

        // Load React Admin JS
        if ($screenId === 'admin_page_wps_data-migration_page') {
            wp_enqueue_script($this->getAssetHandle(), $this->getUrl('migration.js'), [], $this->getVersion(), true);
            wp_localize_script($this->getAssetHandle(), 'wps_react', $this->getLocalizedData($hook));
        }
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
}