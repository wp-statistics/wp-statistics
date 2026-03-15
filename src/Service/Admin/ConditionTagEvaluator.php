<?php

namespace WP_Statistics\Service\Admin;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\User;

class ConditionTagEvaluator
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
        'no-premium' => 'noPremiumUser',
    ];

    /**
     * Check if the current user is an administrator.
     *
     * @return bool
     */
    public static function isAdminUser()
    {
        return User::isAdmin();
    }

    /**
     * Check if the user has a premium license.
     *
     * @return bool
     */
    public static function isPremiumUser()
    {
        // TODO: Check premium status from wp-statistics-premium plugin
        return self::isPremiumPluginActive();
    }

    /**
     * Check if the current version is a free version (no premium).
     *
     * @return bool
     */
    public static function isFreeVersion()
    {
        return !self::isPremiumPluginActive();
    }

    /**
     * Checks if the user does not have a premium license.
     *
     * @return bool
     */
    public static function noPremiumUser()
    {
        return !self::isPremiumPluginActive();
    }

    /**
     * Check if the current plugin version is equal to or higher than the specified version.
     *
     * @param string $version The version to compare against.
     * @return bool
     */
    public static function isVersionOrHigher($version)
    {
        return version_compare(WP_STATISTICS_VERSION, $version, '>=');
    }

    /**
     * Checks if the current WordPress site language matches the given language.
     *
     * @param string $siteLanguage The language code to check.
     * @return bool
     */
    public static function isSiteLanguage($siteLanguage)
    {
        return get_locale() === $siteLanguage;
    }

    /**
     * Checks if the provided country code matches the timezone string set in WordPress.
     *
     * @param string $country The ISO 3166-1 alpha-2 country code.
     * @return bool
     */
    public static function isCountry($country)
    {
        return Helper::getTimezoneCountry() === $country;
    }

    /**
     * Check if the premium plugin is active.
     *
     * @return bool
     */
    private static function isPremiumPluginActive()
    {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        return is_plugin_active('wp-statistics-premium/wp-statistics-premium.php');
    }

    /**
     * Evaluate a given condition tag.
     *
     * @param string $tag The condition tag to check.
     * @param string|null $version Optional version number.
     * @return bool
     */
    public static function checkConditions($tag, $version = null)
    {
        if (strpos($tag, 'is-version-') === 0) {
            $versionNumber = substr($tag, strlen('is-version-'));
            if ($versionNumber) {
                return self::isVersionOrHigher($versionNumber);
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
