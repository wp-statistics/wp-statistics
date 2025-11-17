<?php
namespace WP_Statistics\Service\Translation;

use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;

class TranslationManager
{
    protected const BASE_URL = 'https://translations.veronalabs.com/projects/wp-statistics/';

    protected $pluginHandler;

    public function __construct()
    {
        $this->pluginHandler = new PluginHandler();

        // Download translations when site/user locale changes
        add_action('update_option_WPLANG', [$this, 'onLocaleChange'], 10, 2);
        add_action('update_user_meta', [$this, 'onUserLocaleChange'], 10, 4);

        // Download translations when add-ons are activated/updated
        add_action('activated_plugin', [$this, 'onPluginActivated']);
        add_action('upgrader_process_complete', [$this, 'onPluginUpdated'], 10, 2);

        // Add fallback mechanism for translations
        add_filter('load_textdomain_mofile', [$this, 'fallbackTranslation'], 10, 2);
    }

    /**
     * Get list of active add-ons
     *
     * @return array
     */
    protected function getActiveAddons()
    {
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
    public function onLocaleChange($oldLocale, $newLocale)
    {
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
    public function onUserLocaleChange($metaId, $userId, $metaKey, $metaValue)
    {
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
    public function onPluginActivated($plugin)
    {
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
    public function onPluginUpdated($upgrader, $options)
    {
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
        if (empty($locale)) {
            return;
        }

        if ($this->doesTranslationExist($addon, $locale)) {
            return;
        }

        // Try to download the locale
        $result = $this->downloadTranslationFile($addon, $locale, 'mo');

        // If download failed and locale has a region code, try with base locale (e.g., fa_IR -> fa)
        if (!$result && preg_match('/^([a-z]{2,3})_[A-Z]{2}$/', $locale, $matches)) {
            $baseLocale = $matches[1];
            $this->downloadAddonTranslation($addon, $baseLocale);
        }
    }

    /**
     * Download translation file
     *
     * @param string $addon  Add-on slug
     * @param string $locale Locale
     * @param string $format File format (.mo or .po)
     *
     * @return bool True if download was successful, false otherwise
     */
    protected function downloadTranslationFile($addon, $locale, $format)
    {
        $url = $this->getDownloadUrl($addon, $locale, $format);

        $response = wp_remote_get($url);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $content = wp_remote_retrieve_body($response);

        if (empty($content)) {
            return false;
        }

        $languagesDir = $this->getLanguageDir($addon);

        // Create languages directory if it doesn't exist
        if (!file_exists($languagesDir)) {
            wp_mkdir_p($languagesDir);
        }

        $file = trailingslashit($languagesDir) . $addon . '-' . $locale . '.' . $format;

        // Save the file and log error if it fails
        if (file_put_contents($file, $content) === false) {
            return false;
        }

        return true;
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

    /**
     * Check if translation files exist for an add-on and locale
     *
     * @param string $addon  Add-on slug
     * @param string $locale Locale
     *
     * @return bool
     */
    protected function doesTranslationExist($addon, $locale)
    {
        $moFile = $this->getLanguageDir($addon) . $locale . '.mo';

        return file_exists($moFile);
    }

    /**
     * Fallback mechanism for translations
     * If fa_IR file doesn't exist for add-ons, try to load fa
     *
     * @param string $mofile Path to .mo file
     * @param string $domain Text domain
     *
     * @return string Modified path to .mo file
     */
    public function fallbackTranslation($mofile, $domain)
    {
        // Check if this is one of our add-ons
        if (!array_key_exists($domain, PluginHelper::$plugins)) {
            return $mofile;
        }

        // If the file exists, no fallback needed
        if (file_exists($mofile)) {
            return $mofile;
        }

        // Try fallback to base language (e.g., fa_IR -> fa, es_ES -> es, fil_PH -> fil)
        if (preg_match('/(.+)-([a-z]{2,3})_[A-Z]{2}\.mo$/', $mofile, $matches)) {
            $fallbackFile = $matches[1] . '-' . $matches[2] . '.mo';

            if (file_exists($fallbackFile)) {
                return $fallbackFile;
            }
        }

        return $mofile;
    }
}
