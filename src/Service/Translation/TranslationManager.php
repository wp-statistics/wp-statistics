<?php
namespace WP_Statistics\Service\Translation;

use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;

class TranslationManager
{
    protected $pluginHandler;

    public function __construct()
    {
        $this->pluginHandler = new PluginHandler();

        // Download translations when add-ons are activated/updated
        add_action('activated_plugin', [$this, 'onPluginActivated']);
        add_action('upgrader_process_complete', [$this, 'onPluginUpdated'], 10, 2);

        // Add fallback mechanism for translations
        add_filter('load_textdomain_mofile', [$this, 'fallbackTranslation'], 10, 2);
    }

    /**
     * Handle translation download on add-on activation
     *
     * @param string $plugin Plugin path
     */
    public function onPluginActivated($plugin)
    {
        // Check if activated plugin is one of the add-ons
        foreach (PluginHelper::$plugins as $slug => $title) {
            $addon = $this->pluginHandler->getPluginFile($slug);

            if ($plugin === $addon) {
                TranslationHelper::downloadAddonTranslation($slug, get_locale());
                break;
            }
        }
    }

    /**
     * Handle add-on update
     *
     * @param $upgrader
     * @param $options
     */
    public function onPluginUpdated($upgrader, $options)
    {
        if ($options['type'] !== 'plugin' || $options['action'] !== 'update') {
            return;
        }

        $updatedPlugins = $options['plugins'] ?? [];

        foreach (PluginHelper::$plugins as $slug => $title) {
            $addon = $this->pluginHandler->getPluginFile($slug);

            if (in_array($addon, $updatedPlugins)) {
                TranslationHelper::downloadAddonTranslation($slug, get_locale(), true);
                continue;
            }
        }
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
