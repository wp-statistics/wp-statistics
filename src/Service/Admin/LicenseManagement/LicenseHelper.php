<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use Exception;
use WP_Statistics\Exception\LicenseException;
use WP_STATISTICS\Option;
use WP_STATISTICS\User;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

class LicenseHelper
{
    const LICENSE_OPTION_KEY = 'licenses';

    /**
     * Returns license stored in the WordPress database. By default only valid licenses are returned.
     *
     * @param string $status param to filter licenses by status. Could be: `valid`, `license_expired` or `all`
     * @return array
     */
    public static function getLicenses($status = 'valid')
    {
        $licenses = Option::getOptionGroup(self::LICENSE_OPTION_KEY) ?? [];

        if (!empty($status) && $status !== 'all') {
            $licenses = array_filter($licenses, function ($license) use ($status) {
                return $license['status'] === $status;
            });
        }

        return $licenses;
    }

    /**
     * Shows only the last four characters of a key.
     *
     * @param string $licenseKey
     * @return string
     */
    public static function maskKey($licenseKey)
    {
        return str_repeat('•', max(0, strlen($licenseKey) - 4)) . substr($licenseKey, -4);
    }

    /**
     * Finds the saved key that matches a masked key.
     *
     * @param string $masked
     * @return string|null
     */
    public static function findKeyByMask($masked)
    {
        foreach (array_keys(self::getLicenses('all')) as $key) {
            if (self::maskKey((string) $key) === $masked) {
                return (string) $key;
            }
        }

        return null;
    }

    /**
     * Returns the stored info for a given license key.
     *
     * @param string $licenseKey
     *
     * @return object|false License data if found, false otherwise.
     */
    public static function getLicenseInfo($licenseKey)
    {
        if (empty($licenseKey)) {
            return false;
        }

        $licenses = self::getLicenses('all');
        return $licenses[$licenseKey] ?? false;
    }

    /**
     * Returns the license key that contains the add-on with the given slug, if any.
     *
     * @param string $slug
     *
     * @return string|null License key. `null` if no valid licenses was found for this slug.
     */
    public static function getPluginLicense($slug)
    {
        // Prioritize valid licenses over expired
        $licenses = array_merge(self::getLicenses('valid'), self::getLicenses('license_expired'));

        foreach ($licenses as $key => $license) {
            if (empty($license['products'])) continue;

            if (in_array($slug, $license['products'])) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Retrieve the status of a given plugin slug.
     *
     * @param string $slug The slug of the plugin.
     *
     * @return string|null The status of the plugin, e.g. 'valid', 'expired', or null if no matching license was found.
     */
    public static function getPluginLicenseStatus($slug)
    {
        $licenseKey = self::getPluginLicense($slug);
        $status     = self::getLicenseInfo($licenseKey);

        return $status['status'] ?? null;
    }

    /**
     * Checks if the given plugin slug has a valid license.
     *
     * @param string $slug The slug of the plugin.
     *
     * @return bool True if the plugin has a valid license, false otherwise.
     */
    public static function isPluginLicenseValid($slug)
    {
        $status = self::getPluginLicenseStatus($slug);
        return $status === 'valid';
    }

    /**
     * Checks if the given plugin is expired.
     *
     * @param string $slug The slug of the plugin.
     */
    public static function isPluginLicenseExpired($slug)
    {
        $status = self::getPluginLicenseStatus($slug);
        return $status === 'license_expired';
    }

    /**
     * Store license details in the WordPress database.
     *
     * @param string $licenseKey
     * @param object $license
     */
    public static function storeLicense($licenseKey, $licenseData)
    {
        $data = [
            'status'      => $licenseData->status,
            'type'        => $licenseData->license_details->type ?? null,
            'sku'         => $licenseData->license_details->sku ?? null,
            'max_domains' => $licenseData->license_details->max_domains ?? null,
            'valid_until' => $licenseData->license_details->valid_until ?? null,
            'user'        => $licenseData->license_details->user ?? null,
            'products'    => isset($licenseData->products) ? wp_list_pluck($licenseData->products, 'slug') : null,
        ];

        Option::saveOptionGroup($licenseKey, $data, self::LICENSE_OPTION_KEY);
    }

    /**
     * Update license in the database
     *
     * @param string $licenseKey
     * @param object $license
     */
    public static function updateLicense($licenseKey, $licenseData)
    {
        Option::saveOptionGroup($licenseKey, $licenseData, self::LICENSE_OPTION_KEY);
    }

    /**
     * Removes a license from WordPress database.
     *
     * @param string $licenseKey
     *
     * @return void
     */
    public static function removeLicense($licenseKey)
    {
        Option::deleteOptionGroup($licenseKey, self::LICENSE_OPTION_KEY);
    }

    /**
     * Checks if user has any valid license or not.
     *
     * @return bool
     */
    public static function isValidLicenseAvailable()
    {
        return !empty(self::getLicenses());
    }

    /**
     * Checks all stored licenses to see if any of them is premium.
     *
     * @return string|null License key. `null` if no premium licenses were found.
     */
    public static function isPremiumLicenseAvailable()
    {
        foreach (self::getLicenses() as $key => $data) {
            if (!empty($data['sku']) && $data['sku'] === 'premium') {
                return $key;
            }
        }

        return null;
    }

    /**
     * Only license managers on the main site can act on license notices, so only they see them
     * and only their page loads read the add-on list.
     *
     * @return bool
     */
    public static function canManageLicenses()
    {
        $manageCap = User::ExistCapability(Option::get('manage_capability', 'manage_options'));

        return is_main_site() && $manageCap && current_user_can($manageCap);
    }

    /**
     * True for a site with no license and no add-on installed: the plain free plugin.
     * Its Add-ons screen is a Premium tour, so the menu and title say "Go Premium".
     *
     * @return bool
     */
    public static function isFreeOnly()
    {
        return self::canManageLicenses() && empty(self::getLicenses('all')) && empty(self::getLicenseSummary()['installed']);
    }

    /**
     * One summary of the add-ons and their licenses, shared by the header badge, the sidebar menu,
     * the bell card and the Add-ons screen. Built once per request.
     *
     * @return array total, licensed, installed, missing, active_missing, expired, inactive, not_installed, attention
     */
    public static function getLicenseSummary()
    {
        static $summary = null;

        if ($summary !== null) {
            return $summary;
        }

        try {
            $summary = self::summarizeAddons(PluginHelper::getRemotePlugins());
        } catch (\Throwable $e) {
            return self::summarizeAddons([]);
        }

        return $summary;
    }

    /**
     * Counts add-on states. `attention` is the number shown on the Add-ons link:
     * active add-ons without a valid license plus expired licenses. Switched-off add-ons
     * without a license do not count; they do nothing until someone turns them on.
     *
     * @param iterable $plugins Objects with isInstalled(), isActivated(), isLicenseValid() and isLicenseExpired().
     *
     * @return array
     */
    public static function summarizeAddons($plugins)
    {
        $result = ['total' => 0, 'licensed' => 0, 'installed' => 0, 'missing' => 0, 'active_missing' => 0, 'expired' => 0, 'inactive' => 0, 'not_installed' => 0, 'attention' => 0];

        foreach ($plugins as $plugin) {
            $result['total']++;

            $valid = $plugin->isLicenseValid();

            if ($valid) {
                $result['licensed']++;
            }

            if (!$plugin->isInstalled()) {
                $result['not_installed']++;
                continue;
            }

            $result['installed']++;

            if ($plugin->isLicenseExpired()) {
                $result['expired']++;
            } elseif (!$valid) {
                $result['missing']++;
                if ($plugin->isActivated()) {
                    $result['active_missing']++;
                }
            } elseif (!$plugin->isActivated()) {
                $result['inactive']++;
            }
        }

        $result['attention'] = $result['active_missing'] + $result['expired'];

        return $result;
    }

    /**
     * What the header "Add-ons" link and the sidebar menu entry show. Both read this, so they always match.
     *
     * @return array visible (bool), label (string), count (int, 0 = no number), warning (bool, orange in the sidebar)
     */
    public static function getAddonsMenuState()
    {
        $canManage = self::canManageLicenses();
        $freeOnly  = $canManage && self::isFreeOnly();
        $summary   = $canManage && !$freeOnly ? self::getLicenseSummary() : null;

        return self::resolveAddonsMenuState($freeOnly, self::areAddonPromptsHidden(), $summary);
    }

    /**
     * The menu rules on their own:
     *  - white label: hidden;
     *  - no add-on installed: "Go Premium", orange, no number;
     *  - otherwise "Add-ons", with a number and orange only when something needs fixing
     *    (an active add-on without a license, or an expired license).
     *
     * @param bool       $freeOnly      No add-on installed and no license.
     * @param bool       $promptsHidden White label.
     * @param array|null $summary       {@see summarizeAddons()}; null when the user cannot see license state.
     *
     * @return array
     */
    public static function resolveAddonsMenuState($freeOnly, $promptsHidden, $summary = null)
    {
        if ($promptsHidden) {
            return ['visible' => false, 'label' => __('Add-ons', 'wp-statistics'), 'count' => 0, 'warning' => false];
        }

        if ($freeOnly) {
            return ['visible' => true, 'label' => __('Go Premium', 'wp-statistics'), 'count' => 0, 'warning' => true];
        }

        $count = is_array($summary) ? (int)($summary['attention'] ?? 0) : 0;

        return ['visible' => true, 'label' => __('Add-ons', 'wp-statistics'), 'count' => $count, 'warning' => $count > 0];
    }

    /**
     * Add-on situations returned by {@see getAddonSituation()}.
     */
    const SITUATION_ACTIVE        = 'active';
    const SITUATION_INACTIVE      = 'inactive';
    const SITUATION_IN_PLAN       = 'in_plan';
    const SITUATION_NOT_INSTALLED = 'not_installed';
    const SITUATION_HIDDEN        = 'hidden';

    /**
     * Where the site stands with one add-on, for the small "this needs <add-on>" teasers inside core pages.
     *
     * - `active`:        the add-on runs. Its data shows, so no teaser, whatever the license says
     *                    (an expired license only stops updates; the license notices already say so).
     * - `hidden`:        add-on prompts are switched off (Customization white label or a hidden Add-ons menu).
     * - `inactive`:      installed but turned off.
     * - `in_plan`:       not installed, but a valid license on this site includes it.
     * - `not_installed`: not installed and not licensed.
     *
     * @param string $slug Add-on slug, e.g. `wp-statistics-data-plus`.
     * @param PluginHandler|null $pluginHandler
     *
     * @return string One of the SITUATION_* constants.
     */
    public static function getAddonSituation($slug, $pluginHandler = null)
    {
        $pluginHandler = $pluginHandler ?: new PluginHandler();

        if ($pluginHandler->isPluginActive($slug)) {
            $situation = self::SITUATION_ACTIVE;
        } elseif (self::areAddonPromptsHidden()) {
            $situation = self::SITUATION_HIDDEN;
        } elseif ($pluginHandler->isPluginInstalled($slug)) {
            $situation = self::SITUATION_INACTIVE;
        } elseif (self::isPluginLicenseValid($slug)) {
            $situation = self::SITUATION_IN_PLAN;
        } else {
            $situation = self::SITUATION_NOT_INSTALLED;
        }

        /**
         * Filters the add-on situation used by the inline teasers.
         *
         * @param string $situation One of the LicenseHelper::SITUATION_* constants.
         * @param string $slug Add-on slug.
         */
        return (string)apply_filters('wp_statistics_addon_situation', $situation, $slug);
    }

    /**
     * True when the site owner turned add-on prompts off: Customization's white label, or the Add-ons menu
     * hidden through Customization's "Disable Menus". Teasers then render nothing.
     *
     * @return bool
     */
    public static function areAddonPromptsHidden()
    {
        $hidden = false;

        if (\WP_STATISTICS\Helper::isAddOnActive('customization')) {
            $hidden = Option::getByAddon('wps_white_label', 'customization') == '1'
                || in_array('plugins', (array)Option::getByAddon('disable_menus', 'customization', []), true);
        }

        /**
         * Filters whether add-on prompts (inline teasers) are hidden.
         *
         * @param bool $hidden
         */
        return (bool)apply_filters('wp_statistics_hide_addon_prompts', $hidden);
    }

    /**
     * WordPress Plugins screen narrowed to one installed-but-off add-on, where the site admin turns it on.
     * Our links never activate a plugin themselves.
     *
     * @param string $slug Add-on slug, e.g. `wp-statistics-data-plus`.
     *
     * @return string
     */
    public static function getAddonPluginsPageUrl($slug)
    {
        return add_query_arg(['plugin_status' => 'inactive', 's' => rawurlencode($slug)], admin_url('plugins.php'));
    }

    /**
     * Add-ons screen step that installs the add-ons of a valid license: the download step of the license
     * that includes this add-on, or the license step when there is none.
     *
     * @param string $slug
     *
     * @return string
     */
    public static function getAddonInstallUrl($slug)
    {
        $key = self::isPluginLicenseValid($slug) ? self::getPluginLicense($slug) : null;

        return \WP_STATISTICS\Menus::admin_url('plugins', $key ? ['tab' => 'downloads', 'license_key' => $key] : ['tab' => 'add-license']);
    }

    /**
     * Checks all stored licenses to see if they are valid or not.
     *
     * @return void
     */
    public static function checkLicensesStatus()
    {
        $apiCommunicator = new ApiCommunicator();
        $licenses        = LicenseHelper::getLicenses('all');

        foreach ($licenses as $key => $data) {
            try {
                $licenseData = $apiCommunicator->validateLicense($key);
                LicenseHelper::storeLicense($key, $licenseData);
            } catch (LicenseException $e) {
                // If status is empty, do nothing (probably server error, or connection issue)
                if (!$e->getStatus()) return;

                // If license is expired, update the status
                if ($e->getStatus() === 'license_expired') {
                    $data['status'] = $e->getStatus();
                    LicenseHelper::updateLicense($key, $data);
                    continue; // keep checking the other keys
                }

                // If license is invalid, remove the license
                LicenseHelper::removeLicense($key);
            }
        }
    }

    /**
     * Renders the license status link based on the license state and optional promo banner.
     *
     * @param bool $isPremium Whether the user has a premium license.
     * @param object|null $promoBanner The promotional banner object, if available.
     * @param bool $isMobile Whether the link is for the mobile menu.
     * @return string The HTML for the license status link.
     */
    public static function renderLicenseStatusLink($isPremium, $promoBanner, $isMobile = false)
    {
        $baseUrl = esc_url(UpsellLink::pricing('header'));

        if ($promoBanner && Option::get('display_notifications', false)) {
            $banner = [
                'url'       => esc_url($promoBanner->getUrl()),
                'tooltip'   => $promoBanner->getTooltip(),
                'title'     => esc_html($promoBanner->getTitle()),
                'textColor' => esc_html($promoBanner->getTextColor()),
                'bgColor'   => esc_html($promoBanner->getBackgroundColor()),
                'icon'      => esc_html($promoBanner->getIcon()),
            ];

            $titleText = $banner['tooltip'] ?: $banner['title'];
            $classes   = array_filter([
                'wps-license-status',
                'wps-license-status--custom',
                $banner['tooltip'] ? 'wps-tooltip' : '',
                $banner['textColor'],
                $banner['bgColor'],
            ]);
            $class     = implode(' ', $classes);

            return sprintf(
                '<a href="%s" title="%s" target="_blank" class="%s"><span>%s</span><span>%s</span></a>',
                esc_url($banner['url']),
                esc_attr($titleText),
                esc_attr($class),
                esc_html($banner['icon']),
                esc_html($banner['title'])
            );
        }

        if (!$isPremium && !self::isValidLicenseAvailable()) {
            return sprintf(
                '<a href="%s" target="_blank" rel="noopener" class="wps-license-status wps-license-status--free">%s</a>',
                $baseUrl,
                esc_html__('Upgrade to Premium', 'wp-statistics')
            );
        }

        $licenseText = sprintf(
            /* translators: %1$s: string value, %2$s: string value */
            esc_html__('License: %1$s/%2$s', 'wp-statistics'),
            count(PluginHelper::getLicensedPlugins()),
            count(PluginHelper::$plugins)
        );
        return sprintf(
            '<a href="%s" target="_blank" rel="noopener" class="wps-license-status wps-license-status--valid"><span>%s</span> <span>%s</span></a>',
            $baseUrl,
            $licenseText,
            esc_html__('Upgrade', 'wp-statistics')
        );
    }
}