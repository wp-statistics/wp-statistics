<?php
namespace WP_Statistics\Service\Translation;

class TranslationHelper
{
    protected const BASE_URL = 'https://translations.veronalabs.com/projects/wp-statistics/';

    /**
     * Handles downloading translation for an add-on
     *
     * @param string $slug   Add-on slug
     * @param string $locale Locale
     * @param bool   $force  Whether to force download even if translation exists
     */
    public static function downloadAddonTranslation($addon, $locale = null, $force = false)
    {
        if (empty($locale)) {
            return;
        }

        // If translation already exists skip download, unless it's forced
        if (!$force && self::doesTranslationExist($addon, $locale)) {
            return;
        }

        // Try to download the locale
        $result = self::downloadTranslationFile($addon, $locale, 'mo');

        // If download failed and locale has a region code, try with base locale (e.g., fa_IR -> fa)
        if (!$result && preg_match('/^([a-z]{2,3})_[A-Z]{2}$/', $locale, $matches)) {
            $baseLocale = $matches[1];
            self::downloadAddonTranslation($addon, $baseLocale, $force);
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
    public static function downloadTranslationFile($addon, $locale, $format)
    {
        $url = self::getDownloadUrl($addon, $locale, $format);

        $response = wp_remote_get($url);
        $content  = wp_remote_retrieve_body($response);

        // Check for errors
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200 || empty($content)) {
            return false;
        }

        $languagesDir = self::getLanguageDir($addon);

        // Create languages directory if it doesn't exist
        if (!file_exists($languagesDir)) {
            wp_mkdir_p($languagesDir);
        }

        $file = trailingslashit($languagesDir) . $addon . '-' . $locale . '.' . $format;

        // Save the file and return false if it fails
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
    public static function getLanguageDir($addon)
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
    public static function getDownloadUrl($addon, $locale, $format)
    {
        return sprintf(
            '%s%s/%s/default/export-translations?format=%s',
            self::BASE_URL,
            $addon,
            $locale = str_replace('_', '-', strtolower($locale)),
            $format
        );
    }

    /**
     * Check if translation files exist for an add-on and locale
     *
     * @param string $addon  Add-on slug
     * @param string $locale Locale
     *
     * @return bool
     */
    public static function doesTranslationExist($addon, $locale)
    {
        $moFile = self::getLanguageDir($addon) . '/' . $addon . '-' . $locale . '.mo';

        return file_exists($moFile);
    }
}