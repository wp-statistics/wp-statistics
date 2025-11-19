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
    public static function downloadAddonTranslation($addon, $locale, $force = false)
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
            str_replace('_', '-', strtolower($locale)),
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

    /**
     * Download all translations for an add-on in bulk (ZIP format)
     *
     * @param string $addon Add-on slug
     * @param bool   $force Whether to force download even if translations exist
     *
     * @return bool|WP_Error True if download was successful, WP_Error otherwise
     */
    public static function downloadBulkTranslations($addon, $force = false)
    {
        $languagesDir = self::getLanguageDir($addon);

        // Create languages directory if it doesn't exist
        if (!file_exists($languagesDir)) {
            wp_mkdir_p($languagesDir);
        }

        // Get bulk export URL
        $url = self::getBulkExportUrl($addon);

        // Download the ZIP file
        $response = wp_remote_get($url, ['timeout' => 60]);

        // Check for errors
        if (is_wp_error($response)) {
            return new \WP_Error('download_failed', sprintf(__('Failed to download translations: %s', 'wp-statistics'), $response->get_error_message()));
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        if ($responseCode !== 200) {
            return new \WP_Error('download_failed', sprintf(__('Failed to download translations: HTTP %d', 'wp-statistics'), $responseCode));
        }

        $content = wp_remote_retrieve_body($response);
        if (empty($content)) {
            return new \WP_Error('download_failed', __('Failed to download translations: Empty response', 'wp-statistics'));
        }

        // Save ZIP file temporarily
        $tmpFile = trailingslashit($languagesDir) . $addon . '-translations.zip';
        if (file_put_contents($tmpFile, $content) === false) {
            return new \WP_Error('save_failed', __('Failed to save translation archive', 'wp-statistics'));
        }

        // Extract the ZIP file
        $extractResult = self::extractTranslationArchive($tmpFile, $languagesDir);

        // Clean up temporary ZIP file
        if (file_exists($tmpFile)) {
            @unlink($tmpFile);
        }

        return $extractResult;
    }

    /**
     * Extract translation ZIP archive
     *
     * @param string $zipFile Path to ZIP file
     * @param string $destination Destination directory
     *
     * @return bool|WP_Error True if extraction was successful, WP_Error otherwise
     */
    protected static function extractTranslationArchive($zipFile, $destination)
    {
        // Check if ZIP extension is available
        if (!class_exists('ZipArchive')) {
            return new \WP_Error('zip_not_available', __('Failed to extract translations: ZIP extension not available', 'wp-statistics'));
        }

        $zip = new \ZipArchive();
        $result = $zip->open($zipFile);

        if ($result !== true) {
            return new \WP_Error('zip_open_failed', sprintf(__('Failed to extract translations: Cannot open archive (error code: %d)', 'wp-statistics'), $result));
        }

        // Extract only .mo and .po files
        $extracted = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            
            // Only extract .mo and .po files
            if (preg_match('/\.(mo|po)$/i', $filename)) {
                $fileContent = $zip->getFromIndex($i);
                if ($fileContent !== false) {
                    $targetPath = trailingslashit($destination) . basename($filename);
                    if (file_put_contents($targetPath, $fileContent) !== false) {
                        $extracted = true;
                    }
                }
            }
        }

        $zip->close();

        if (!$extracted) {
            return new \WP_Error('no_translations_extracted', __('Failed to extract translations: No translation files found in archive', 'wp-statistics'));
        }

        return true;
    }

    /**
     * Get bulk export URL for downloading all translations
     *
     * @param string $addon Add-on slug
     *
     * @return string
     */
    public static function getBulkExportUrl($addon)
    {
        return sprintf(
            'https://translations.veronalabs.com/bulk-export/%s/?format=mo',
            $addon
        );
    }
}