<?php

namespace WP_Statistics\Abstracts;

/**
 * Admin Assets Abstract Class
 *
 * Base class for managing WordPress admin assets (CSS/JS) in WP Statistics plugin.
 * Provides common functionality for loading and enqueuing styles and scripts.
 *
 * @package WP_STATISTICS\Abstracts
 * @since   15.0.0
 */
abstract class BaseAdminAssets
{
    /**
     * Asset handle name prefix
     *
     * @var string
     * @since 15.0.0
     */
    private $prefix = 'wp-statistics-admin';

    /**
     * Asset context (e.g., 'react', 'admin', 'dashboard')
     *
     * @var string
     * @since 15.0.0
     */
    private $context = '';

    /**
     * Asset handle name
     *
     * @var string
     * @since 15.0.0
     */
    private $assetHandle = '';

    /**
     * Plugin URL
     *
     * @var string
     * @since 15.0.0
     */
    private $pluginUrl = WP_STATISTICS_URL;

    /**
     * Asset version
     *
     * @var string
     * @since 15.0.0
     */
    private $assetVersion = WP_STATISTICS_VERSION;

    /**
     * Asset directory
     *
     * @var string
     * @since 15.0.0
     */
    private $assetDir = 'assets';

    /**
     * Set the asset directory
     *
     * @param string $dir Asset directory path
     * @return void
     * @since 15.0.0
     */
    protected function setAssetDir(string $dir)
    {
        $this->assetDir = $dir;
    }

    /**
     * Get asset directory
     *
     * @return string Asset directory path
     * @since 15.0.0
     */
    protected function getAssetDir()
    {
        return $this->assetDir;
    }

    /**
     * Set the asset context and handle
     *
     * @param string $context Asset context (e.g., 'react', 'admin', 'dashboard')
     * @return void
     * @since 15.0.0
     */
    protected function setContext($context)
    {
        $this->context     = $context;
        $this->assetHandle = $this->prefix . '-' . $context;
    }

    /**
     * Get the asset context
     *
     * @return string The asset context
     * @since 15.0.0
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
     * @since 15.0.0
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
     * @since 15.0.0
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
     * @param string $file_name File name
     * @return string Asset URL
     * @since 15.0.0
     */
    protected function getUrl($file_name)
    {
        return $this->pluginUrl . $this->assetDir . '/' . $file_name;
    }

    /**
     * Get localized data for JavaScript
     *
     * @param string $hook Current admin page hook
     * @return array Localized data for JavaScript
     * @since 15.0.0
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
     * @since 15.0.0
     */
    protected function getI18nStrings()
    {
        return [];
    }

    /**
     * Initialize the assets manager
     *
     * @return void
     * @since 15.0.0
     */
    abstract public function __construct();

    /**
     * Register and enqueue admin styles
     *
     * @return void
     * @since 15.0.0
     */
    abstract public function adminStyles();

    /**
     * Register and enqueue admin scripts
     *
     * @param string $hook Current admin page hook
     * @return void
     * @since 15.0.0
     */
    abstract public function adminScripts($hook);
}