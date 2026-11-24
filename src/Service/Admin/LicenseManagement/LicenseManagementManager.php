<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Exception;
use WP_Statistics;
use WP_Statistics\Exception\LicenseException;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginActions;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginUpdater;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_STATISTICS\User;

/**
 * LicenseManagementManager Class
 *
 * Manages the activation and validation of licenses for WP Statistics add-ons.
 * Provides methods to handle license keys and ensure proper functionality of premium features.
 *
 * @package   LicenseManagement
 * @version   1.0.1
 * @since     14.0
 * @author    Foad
 */
class LicenseManagementManager
{
    /** @var ApiCommunicator */
    private $apiCommunicator;

    private $pluginHandler;
    private $handledPlugins = [];

    public function __construct()
    {
        $this->apiCommunicator = new ApiCommunicator();
        $this->pluginHandler   = new PluginHandler();

        // Initialize the necessary components.
        $this->initActionCallbacks();

        add_action('init', [$this, 'initPluginUpdaters']);
        add_action('admin_init', [$this, 'showPluginActivationNotice']);
        add_action('admin_init', [$this, 'redirectLockedViewOfActiveAddon']);
        add_filter('wp_statistics_enable_upgrade_to_bundle', [$this, 'showUpgradeToBundle']);
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);

        (new InlineTeaser())->register();
    }

    /**
     * Where a "locked" add-on page goes once the add-on runs, keyed by the page `type`.
     *
     * @return array type => [add-on slug, URL of the real screen]
     */
    public static function getLockedViewTargets()
    {
        return [
            'locked-real-time'  => ['wp-statistics-realtime-stats', admin_url('admin.php?page=wp_statistics_realtime_stats')],
            'locked-mini-chart' => ['wp-statistics-mini-chart', \WP_STATISTICS\Menus::admin_url('settings', ['tab' => 'mini-chart-settings'])],
        ];
    }

    /**
     * The URL a locked add-on page should send the user to, or null to show the page.
     * Only when the add-on runs: a teaser for something already working is wrong, white label or not.
     *
     * @param string $type The `type` query arg of the Add-ons page.
     * @param PluginHandler|null $pluginHandler
     *
     * @return string|null
     */
    public static function getLockedViewRedirect($type, $pluginHandler = null)
    {
        $targets = self::getLockedViewTargets();

        if (!isset($targets[$type])) {
            return null;
        }

        list($slug, $url) = $targets[$type];
        $pluginHandler    = $pluginHandler ?: new PluginHandler();

        return $pluginHandler->isPluginActive($slug) ? $url : null;
    }

    public function redirectLockedViewOfActiveAddon()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        $type = isset($_GET['type']) ? sanitize_key(wp_unslash($_GET['type'])) : '';
        // phpcs:enable

        if ($page !== \WP_STATISTICS\Menus::get_page_slug('plugins') || $type === '' || wp_doing_ajax()) {
            return;
        }

        $url = self::getLockedViewRedirect($type, $this->pluginHandler);

        if ($url) {
            wp_safe_redirect($url);
            exit;
        }
    }

    public function addMenuItem($items)
    {
        // Same rules as the header link: "Go Premium" only when no add-on is installed, orange only
        // when it asks for a click (free-only site, active add-on without a license, expired license).
        $state      = LicenseHelper::getAddonsMenuState();
        $label      = $state['label'];
        $labelClass = $state['warning'] ? 'wps-text-warning' : 'wps-addons-menu';

        $items['plugins'] = [
            'sub'      => 'overview',
            'title'    => $label,
            'name'     => '<span class="' . $labelClass . '">' . $label . '</span>',
            'page_url' => 'plugins',
            'callback' => LicenseManagerPage::class,
            'cap'      => User::ExistCapability(Option::get('manage_capability', 'manage_options')),
            'priority' => 90,
            // White label: no Add-ons entry (its pages link to wp-statistics.com). The page still opens by URL for license work.
            'hidden'   => !$state['visible'],
        ];
        return $items;
    }

    /**
     * Initialize AJAX callbacks for various license management actions.
     */
    private function initActionCallbacks()
    {
        add_filter('wp_statistics_ajax_list', [new PluginActions(), 'registerAjaxCallbacks']);
    }

    /**
     * Initialize the PluginUpdater for all stored licenses.
     */
    public function initPluginUpdaters()
    {
        $storedLicenses = LicenseHelper::getLicenses();

        if (empty($storedLicenses)) return;

        foreach ($storedLicenses as $licenseKey => $licenseData) {
            foreach ($licenseData['products'] as $productSlug) {
                // Avoid duplicate handling for the same product
                if (!in_array($productSlug, $this->handledPlugins)) {
                    $this->initPluginUpdaterIfValid($productSlug, $licenseKey);
                }
            }
        }
    }

    /**
     * Initialize PluginUpdater for a specific product and license key.
     *
     * @param string $pluginSlug The slug of the plugin (e.g., 'wp-statistics-data-plus').
     * @param string $licenseKey The license key for the product.
     */
    private function initPluginUpdaterIfValid($pluginSlug, $licenseKey)
    {
        try {
            if (!$this->pluginHandler->isPluginActive($pluginSlug)) {
                return;
            }

            // Get the dynamic version of the plugin
            $pluginData = $this->pluginHandler->getPluginData($pluginSlug);
            if (!$pluginData) {
                /* translators: %s: string value */
                throw new Exception(sprintf(__('Plugin data not found for: %s', 'wp-statistics'), $pluginSlug));
            }

            // Initialize PluginUpdater with the version and license key
            $pluginUpdater = new PluginUpdater($pluginSlug, $pluginData['Version'], $licenseKey);
            $pluginUpdater->handle();

            $this->handledPlugins[] = $pluginSlug;

        } catch (Exception $e) {
            WP_Statistics::log(sprintf('Failed to initialize PluginUpdater for %s: %s', $pluginSlug, $e->getMessage()));
        }
    }

    /**
     * Loop through plugins and show license notice for those without a valid license
     */
    public function showPluginActivationNotice()
    {
        global $pagenow;

        $plugins = $this->pluginHandler->getInstalledPlugins();

        // Return early on the plugins page, or if the plugins array is empty
        if (empty($plugins) || $pagenow != 'plugins.php') {
            return;
        }

        foreach ($plugins as $plugin) {
            if (!LicenseHelper::isPluginLicenseValid($plugin['TextDomain'])) {
                $pluginUpdater = new PluginUpdater($plugin['TextDomain'], $plugin['Version']);
                $pluginUpdater->handleLicenseNotice();
            }
        }

        // Force a check for updates (prevents showing update notice for plugins without a valid license)
        delete_site_transient('update_plugins');
    }

    /**
     * Show the "Upgrade To Premium" only if the user has a premium license.
     *
     * @return bool
     */
    public function showUpgradeToBundle()
    {
        return LicenseHelper::isPremiumLicenseAvailable() ? false : true;
    }
}
