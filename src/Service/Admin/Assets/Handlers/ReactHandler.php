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

        // Load React Admin CSS on all WP Statistics pages for now
        $this->enqueueDistAssets('css');
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

        // Load React Admin JS on all WP Statistics pages for now
        $this->enqueueDistAssets('js', $hook);
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
     * Enqueue all assets from dist folder
     *
     * @param string $type Asset type ('css' or 'js')
     * @param string|null $hook Current admin page hook (for JS localization)
     * @return void
     */
    protected function enqueueDistAssets($type, $hook = null)
    {
        $distPath = trailingslashit(WP_STATISTICS_DIR) . trailingslashit($this->getAssetDir()) . 'assets';
        $pattern  = trailingslashit($distPath) . '*.' . $type;
        $files = glob($pattern);

        $index = 0;
        foreach ($files as $file) {
            $filename = basename($file);
            $handle = $this->getAssetHandle() . '-' . $index;
            $url = $this->getUrl('assets/' . $filename);

            // Debug URL
            add_action('admin_head', function() use ($filename, $url, $handle) {
                echo "<!-- WP Statistics React: File: $filename, Handle: $handle, URL: " . ($url ?: 'EMPTY') . " -->\n";
            });

            if (empty($url)) {
                continue;
            }

            if ($type === 'css') {
                wp_enqueue_style($handle, $url, [], $this->getVersion());
            } else {
                wp_enqueue_script($handle, $url, [], $this->getVersion(), null, true);


                // Localize only the first script
                if ($index === 0 && $hook !== null) {
                    wp_localize_script($handle, 'wps_react', $this->getLocalizedData($hook));
                }
            }

            $index++;
        }

          add_filter('script_loader_tag', function($tag, $handle, $src) {
            if (strpos($handle, 'wp-statistics-admin') === 0) {
                        return str_replace('<script ', '<script type="module" ', $tag);
                    }
                    return $tag;
                

            }, 10, 3);
    }
}
