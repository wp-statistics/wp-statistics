<?php

namespace WP_Statistics\Service\Admin\Assets\Handlers;

use WP_Statistics\Abstracts\BaseAdminAssets;
use WP_STATISTICS\Helper;

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
     * @since 15.0.0
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
     * @since 15.0.0
     */
    public function adminStyles()
    {
        // Get Current Screen ID
        $screenId = Helper::get_screen_id();
    }

    /**
     * Register and enqueue React admin scripts
     *
     * @param string $hook Current admin page hook
     * @return void
     * @since 15.0.0
     */
    public function adminScripts($hook)
    {
        // Get Current Screen ID
        $screenId = Helper::get_screen_id();

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
     * @since 15.0.0
     */
    protected function getLocalizedData($hook)
    {
        $list = parent::getLocalizedData($hook);

        // React-specific options
        $list['options'] = [];

        // React-specific translations
        $list['i18n'] = $this->getI18nStrings();

        return apply_filters('wp_statistics_react_assets', $list);
    }

    /**
     * Get internationalization strings for React
     *
     * @return array Array of translated strings
     * @since 15.0.0
     */
    protected function getI18nStrings()
    {
        return [];
    }
}