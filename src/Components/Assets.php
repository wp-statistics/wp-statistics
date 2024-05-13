<?php

namespace WP_Statistics\Components;

class Assets
{
    /**
     * Assets folder name
     *
     * @var string
     */
    public static $asset_dir = 'assets';

    /**
     * Plugin Url in WordPress
     *
     * @var string
     * @example http://site.com/wp-content/plugins/my-plugin/
     */
    public static $plugin_url = WP_STATISTICS_URL;

    /**
     * Enqueue a script.
     *
     * @param string $handle The script handle.
     * @param string $src The source URL of the script.
     * @param array $deps An array of script dependencies.
     * @param array $localize An array of data to be localized.
     * @param bool $inFooter Whether to enqueue the script in the footer.
     * @return void
     * @example Assets::script('admin', 'dist/admin.js', ['jquery'], ['foo' => 'bar'], true);
     */
    public static function script($handle, $src, $deps = [], $localize = [], $inFooter = false)
    {
        $object = self::getObject($handle);
        $handle = self::getHandle($handle);

        wp_enqueue_script($handle, self::getSrc($src), $deps, WP_STATISTICS_VERSION, $inFooter);

        if ($localize) {
            $localize = apply_filters("wp_statistics_localize_{$handle}", $localize);

            wp_localize_script($handle, $object, $localize);
        }
    }

    /**
     * Register a script.
     *
     * @param string $handle The script handle.
     * @param string $src The source URL of the script.
     * @param array $deps An array of script dependencies.
     * @param string|null $version Optional. The version of the script. Defaults to plugin version.
     * @param bool $inFooter Whether to enqueue the script in the footer.
     * @return void
     * @example Assets::registerScript('chartjs', 'js/chart.min.js', [], '3.7.1');
     */
    public static function registerScript($handle, $src, $deps = [], $version = null, $inFooter = false)
    {
        // Get the handle for the script
        $handle = self::getHandle($handle);

        // Get the version of the script, if not provided, use the default version
        if ($version === null) {
            $version = WP_STATISTICS_VERSION;
        }

        // Register the script with WordPress
        wp_register_script($handle, self::getSrc($src), $deps, $version, $inFooter);
    }

    /**
     * Enqueue a style.
     *
     * @param string $handle The style handle.
     * @param string $src The source URL of the style.
     * @param array $deps An array of style dependencies.
     * @param string $media The context which style needs to be loaded: all, print, or screen
     * @return void
     * @example Assets::style('admin', 'dist/admin.css', ['jquery'], 'all');
     */
    public static function style($handle, $src, $deps = [], $media = 'all')
    {
        wp_enqueue_style(self::getHandle($handle), self::getSrc($src), $deps, WP_STATISTICS_VERSION, $media);
    }

    /**
     * Get the handle for the script/style.
     *
     * @param string $handle The script/style handle.
     * @return string
     */
    private static function getHandle($handle)
    {
        return sprintf('wp-statistics-%s', strtolower($handle));
    }

    /**
     * Get the source URL for the script/style.
     *
     * @param string $src The source URL.
     * @return string
     */
    private static function getSrc($src)
    {
        return self::$plugin_url . self::$asset_dir . '/' . $src;
    }

    /**
     * Get the object name for script localization.
     *
     * @param string $handle The script handle.
     * @return string
     */
    private static function getObject($handle)
    {
        $parts          = explode('-', $handle);
        $camelCaseParts = array_map('ucfirst', $parts);

        return 'WP_Statistics_' . implode('_', $camelCaseParts) . '_Object';
    }
}
