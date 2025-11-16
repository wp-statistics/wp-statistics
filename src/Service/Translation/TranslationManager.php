<?php
namespace WP_Statistics\Service\Translation;

use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;

class TranslationManager
{
    /**
     * GlotPress base URL
     */
    const BASE_URL = 'https://translations.veronalabs.com/projects/wp-statistics/';

    protected $pluginHandler;

    public function __construct() {
        $this->pluginHandler = new PluginHandler();

        // Download translations when site/user locale changes
        add_action('update_option_WPLANG', [$this, 'onLocaleChange'], 10, 2);
        add_action('update_user_meta', [$this, 'onUserLocaleChange'], 10, 4);

        // Download translations when add-ons are activated/updated
        add_action('activated_plugin', [$this, 'onPluginActivated']);
        add_action('upgrader_process_complete', [$this, 'onPluginUpdated'], 10, 2);
    }

    /**
     * Get list of active add-ons
     *
     * @return array
     */
    protected function getActiveAddons() {
        $activeAddons = [];

        foreach (PluginHelper::$plugins as $slug => $title) {
            if ($this->pluginHandler->isPluginActive($slug)) {
                $activeAddons[] = $slug;
            }
        }

        return $activeAddons;
    }

    /**
     * Handle locale change
     *
     * @param string $oldLocale Old locale
     * @param string $newLocale New locale
     */
    public function onLocaleChange($oldLocale, $newLocale) {
        if ($oldLocale === $newLocale || empty($newLocale)) {
            return;
        }

        // Download translations for all active add-ons in new locale
        foreach ($this->getActiveAddons() as $slug) {
            $this->downloadAddonTranslation($slug, $newLocale);
        }
    }

    /**
     * Handle user locale change
     *
     * @param int    $metaId    ID of updated metadata entry
     * @param int    $userId    User ID
     * @param string $metaKey   Meta key
     * @param mixed  $metaValue Meta value
     */
    public function onUserLocaleChange($metaId, $userId, $metaKey, $metaValue) {
        if ($metaKey !== 'locale') {
            return;
        }

        foreach ($this->getActiveAddons() as $slug) {
            $this->downloadAddonTranslation($slug, $metaValue);
        }
    }

    /**
     * Handle add-on activation
     *
     * @param string $plugin Plugin path
     */
    public function onPluginActivated($plugin) {
        // Check if activated plugin is one of our known add-ons
        foreach (PluginHelper::$plugins as $slug => $title) {
            $pluginFile = $this->pluginHandler->getPluginFile($slug);

            if ($plugin === $pluginFile) {
                $this->downloadAddonTranslation($slug);
                break;
            }
        }
    }

    /**
     * Handle add-on update
     *
     * @param \WP_Upgrader $upgrader
     * @param array        $options
     */
    public function onPluginUpdated($upgrader, $options) {
        if ($options['type'] !== 'plugin' || $options['action'] !== 'update') {
            return;
        }

        $plugins = $options['plugins'] ?? [];

        foreach (PluginHelper::$plugins as $slug => $title) {
            $pluginFile = $this->pluginHandler->getPluginFile($slug);

            foreach ($plugins as $plugin) {
                if ($plugin === $pluginFile) {
                    $this->downloadAddonTranslation($slug);
                    break;
                }
            }
        }
    }

    /**
     * Handles downloading translation for an add-on
     *
     * @param string      $slug   Add-on slug
     * @param string|null $locale Locale to download (defaults to current)
     */
    protected function downloadAddonTranslation($addon, $locale = null)
    {
        if (empty($locale)) return;

        // Download .mo file
        $this->downloadTranslationFile($addon, $locale, 'mo');

        // Download .po file
        $this->downloadTranslationFile($addon, $locale, 'po');
    }

    /**
     * Download translation file
     *
     * @param string      $addon        Add-on slug
     * @param string      $locale       Locale
     * @param string      $format       File format (.mo or .po)
     * @param string      $languagesDir Directory to save the file
     */
    protected function downloadTranslationFile($addon, $locale, $format)
    {
        $url = $this->getDownloadUrl($addon, $locale, $format);

        $response = wp_remote_get($url);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            WP_Statistics()->log("Download failed for `$addon` ($locale) translation.", 'error');
            return;
        }

        $content = wp_remote_retrieve_body($response);

        if (!empty($content)) {
            $languagesDir = $this->getLanguageDir($addon);

            // Create languages directory if it doesn't exist
            if (!file_exists($languagesDir)) {
                wp_mkdir_p($languagesDir);
            }

            $file = trailingslashit($languagesDir) . $addon . '-' . $locale . '.' . $format;

            // Save the file and log error if it fails
            if (file_put_contents($file, $content) === false) {
                WP_Statistics()->log("Failed to create translation files for `$addon`.", 'error');
            }
        }
    }

    /**
     * Get language directory for an add-on
     * @param string $addon
     *
     * @return string
     */
    protected function getLanguageDir($addon)
    {
        $pluginDir = WP_PLUGIN_DIR . '/'. $addon;
        return trailingslashit($pluginDir) . 'languages';
    }

    /**
     * Get download URL for translation
     *
     * @param string $addon  Add-on slug
     * @param string $locale Locale
     * @param string $format File format
     *
     * @return string
     */
    protected function getDownloadUrl($addon, $locale, $format)
    {
        return sprintf(
            '%s%s/%s/default/export-translations?format=%s',
            self::BASE_URL,
            $addon,
            $this->normalizeLocale($locale),
            $format
        );
    }

    /**
     * Normalize locale string
     * @param string $locale
     * @return string
     */
    protected function normalizeLocale($locale)
    {
        $locale = str_replace('_', '-', $locale);
        $locale = strtolower($locale);

        return $locale;
    }
}
