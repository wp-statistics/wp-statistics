<?php

namespace WP_Statistics\Abstracts;

/**
 * Assets Abstract Class
 *
 * Base class for managing assets in WP Statistics plugin.
 * Provides common functionality for loading and enqueuing styles and scripts.
 *
 * @package WP_STATISTICS\Abstracts
 * @since   15.0.0
 */
abstract class BaseAssets
{
    /**
     * Asset handle name prefix
     *
     * @var string
     */
    private $prefix = 'wp-statistics-admin';

    /**
     * Asset context (e.g., 'react', 'admin', 'dashboard')
     *
     * @var string
     */
    private $context = '';

    /**
     * Asset handle name
     *
     * @var string
     */
    private $assetHandle = '';

    /**
     * Plugin URL
     *
     * @var string
     */
    private $pluginUrl = WP_STATISTICS_URL;

    /**
     * Asset version
     *
     * @var string
     */
    private $assetVersion = WP_STATISTICS_VERSION;

    /**
     * Asset directory
     *
     * @var string
     */
    private $assetDir = 'public/legacy';

    /**
     * Set the asset directory
     *
     * @param string $dir Asset directory path
     * @return void
     */
    protected function setAssetDir(string $dir)
    {
        $this->assetDir = $dir;
    }

    /**
     * Get asset directory
     *
     * @return string Asset directory path
     */
    protected function getAssetDir()
    {
        return $this->assetDir;
    }

    /**
     * get the plugin URL
     *
     * @return string Plugin URL
     */
    protected function getPluginUrl()
    {
        return $this->pluginUrl;
    }

    /**
     * Set the asset context and handle
     *
     * @param string $context Asset context (e.g., 'react', 'admin', 'dashboard')
     * @return void
     */
    protected function setContext($context)
    {
        $this->context     = $context;
        $this->assetHandle = $this->prefix . '-' . $context;
    }

    /**
     * Set the asset handle prefix
     *
     * @param string $prefix Asset handle prefix
     * @return void
     */
    protected function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get the asset handle prefix
     *
     * @return string The asset handle prefix
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get the asset context
     *
     * @return string The asset context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get the asset handle name
     *
     * @param string $suffix Optional suffix to append to the handle
     * @return string The complete handle name
     */
    public function getAssetHandle($suffix = '')
    {
        return $suffix ? $this->assetHandle . '-' . $suffix : $this->assetHandle;
    }

    /**
     * Get asset version
     *
     * @param string|bool $version Version number or false to use default
     * @return string Asset version
     */
    public function getVersion($version = false)
    {
        if ($version === false) {
            $version = $this->assetVersion;
        }

        return $version;
    }

    /**
     * Get asset URL
     *
     * @param string $fileName File name
     * @param bool   $relativePath If true, return the relative file path instead of URL
     * @return string Asset URL
     */
    protected function getUrl($fileName, $relativePath = false)
    {
        $relative = trailingslashit($this->assetDir) . ltrim($fileName, '/');
        $filePath = wp_normalize_path(trailingslashit(WP_STATISTICS_DIR) . $relative);

        if ($relativePath) {
            return $filePath;
        }

        if (file_exists($filePath)) {
            return trailingslashit($this->pluginUrl) . $relative;
        }

        return '';
    }

    /**
     * Get localized data for JavaScript
     *
     * @param string $hook Current admin page hook
     * @return array Localized data for JavaScript
     */
    protected function getLocalizedData($hook)
    {
        return [
            'options' => [
                'url'     => admin_url(),
                'nonce'   => wp_create_nonce('wp_statistics_nonce'),
                'isRtl'   => is_rtl(),
                'isDebug' => defined('WP_DEBUG') && WP_DEBUG,
                'version' => WP_STATISTICS_VERSION
            ],
            'i18n'    => $this->getI18nStrings()
        ];
    }

    /**
     * Get internationalization strings
     *
     * @return array Array of translated strings
     */
    protected function getI18nStrings()
    {
        return [];
    }

    /**
     * Initialize the assets manager
     *
     * @return void
     */
    abstract public function __construct();

    /**
     * Register and enqueue styles.
     *
     * @return void
     */
    abstract public function styles();

    /**
     * Register and enqueue scripts.
     *
     * @param string $hook Current admin page hook (optional)
     * @return void
     */
    abstract public function scripts($hook = '');
}