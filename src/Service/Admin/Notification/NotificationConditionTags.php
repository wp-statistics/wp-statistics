<?php

namespace WP_Statistics\Service\Admin\Notification;

use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_STATISTICS\Helper;
use WP_STATISTICS\User;

class NotificationConditionTags
{
    /**
     * Array mapping condition tags to their respective methods.
     *
     * @var array
     */
    private static $tags = [
        'is-admin'   => 'isAdminUser',
        'is-premium' => 'isPremiumUser',
        'is-free'    => 'isFreeVersion',
        'has-addon'  => 'hasAddon',
        'no-premium' => 'noPremiumUser',
    ];

    /**
     * Plugin handler instance.
     *
     * @var PluginHandler
     */
    private static $pluginHandler;

    /**
     * Initialize the plugin handler.
     *
     * @return PluginHandler
     */
    private static function getPluginHandler()
    {
        if (!self::$pluginHandler) {
            self::$pluginHandler = new PluginHandler();
        }
        return self::$pluginHandler;
    }


    /**
     * Check if the current user is an administrator.
     *
     * @return bool True if the user is an admin, false otherwise.
     */
    public static function isAdminUser()
    {
        return User::isAdmin();
    }

    /**
     * Check if the user has a premium license.
     *
     * @return bool True if a premium license is available, false otherwise.
     */
    public static function isPremiumUser()
    {
        return LicenseHelper::isPremiumLicenseAvailable() ? true : false;
    }

    /**
     * Check if the current version is a free version (no premium license).
     *
     * @return bool True if the version is free, false otherwise.
     */
    public static function isFreeVersion()
    {
        $pluginHandler = self::getPluginHandler();
        foreach (PluginHelper::$plugins as $plugin => $title) {
            if ($pluginHandler->isPluginActive($plugin)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if any add-on is currently active.
     *
     * @return bool
     */
    public static function hasAddon()
    {
        $pluginHandler = self::getPluginHandler();
        foreach (PluginHelper::$plugins as $plugin => $title) {
            if ($pluginHandler->isPluginActive($plugin)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the user does not have a premium license.
     *
     * @return bool
     */
    public static function noPremiumUser()
    {
        return !LicenseHelper::isPremiumLicenseAvailable() ? true : false;
    }

    /**
     * Check if a specific addon is active.
     *
     * @param string $addon The addon name.
     * @return bool True if the addon is active, false otherwise.
     */
    public static function isAddon($addon)
    {
        $pluginHandler = self::getPluginHandler();
        return $pluginHandler->isPluginActive($addon);
    }

    /**
     * Check if a specific addon is inactive.
     *
     * @return bool
     */
    public static function noAddon($addon)
    {
        $pluginHandler = self::getPluginHandler();
        return !$pluginHandler->isPluginActive($addon);
    }

    /**
     * Check if the current plugin version is equal to or higher than the specified version.
     *
     * @param string $version The version to compare against.
     * @return bool True if the current version is equal to or higher, false otherwise.
     */
    public static function isVersionOrHigher($version)
    {
        $currentVersion = WP_STATISTICS_VERSION;

        if (version_compare($currentVersion, $version, '>=')) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the current WordPress site language matches the given language.
     *
     * @param string $siteLanguage The language code to check (e.g., 'en_US', 'fr_FR').
     *
     * @return bool Returns true if the site language matches, otherwise false.
     */
    public static function isSiteLanguage($siteLanguage)
    {
        $locale = get_locale();

        if ($locale === $siteLanguage) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the provided country code matches the timezone string set in WordPress.
     *
     * @param string $country The ISO 3166-1 alpha-2 country code to check against the WordPress timezone.
     *
     * @return bool True if a matching country and timezone are found, false otherwise.
     */
    public static function isCountry($country)
    {
        $countryCode = Helper::getTimezoneCountry();

        if ($countryCode === $country) {
            return true;
        }

        return false;
    }

    /**
     * Evaluate a given condition tag and return whether it is met.
     *
     * @param string $tag The condition tag to check.
     * @param string|null $version Optional version number for version-related checks.
     * @return bool True if the condition is met, false otherwise.
     */
    public static function checkConditions($tag, $version = null)
    {
        if (strpos($tag, 'is-version-') === 0) {
            $versionNumber = substr($tag, strlen('is-version-'));
            if ($versionNumber) {
                return self::isVersionOrHigher($versionNumber);
            }
        }

        if (strpos($tag, 'has-addon-') === 0) {
            $addon = substr($tag, strlen('has-addon-'));
            if ($addon) {
                return self::isAddon('wp-statistics-' . $addon);
            }
        }

        if (strpos($tag, 'no-addon-') === 0) {
            $addon = substr($tag, strlen('no-addon-'));
            if ($addon) {
                return self::noAddon('wp-statistics-' . $addon);
            }
        }

        if (strpos($tag, 'is-locale-') === 0) {
            $locale = substr($tag, strlen('is-locale-'));
            if ($locale) {
                return self::isSiteLanguage($locale);
            }
        }

        if (strpos($tag, 'is-country-') === 0) {
            $country = substr($tag, strlen('is-country-'));
            if ($country) {
                return self::isCountry($country);
            }
        }

        if (array_key_exists($tag, self::$tags)) {
            $method = self::$tags[$tag];
            return self::$method();
        }

        return false;
    }
}