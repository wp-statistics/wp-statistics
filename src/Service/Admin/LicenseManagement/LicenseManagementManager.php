<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

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

        add_action('init', [$this, 'constLicenseValidation']);
        add_action('init', [$this, 'initPluginUpdaters']);
        add_action('admin_init', [$this, 'showPluginActivationNotice']);
        add_filter('wp_statistics_enable_upgrade_to_bundle', [$this, 'showUpgradeToBundle']);
        add_filter('wp_statistics_admin_menu_list', [$this, 'addMenuItem']);
    }

    /**
     * Validates licenses for active plugins using constants defined in wp-config.php.
     *
     * This method loops through a list of expected plugin license constants,
     * and for each active plugin with a defined constant and available license key,
     * it triggers license validation via the API communicator.
     *
     * @return void
     *
     * @throws Exception if the API call fails
     */
    public function constLicenseValidation()
    {
        // Check if the license is defined
        if (!defined('WP_STATISTICS_LICENSE')) return;

        $licenses = WP_STATISTICS_LICENSE;

        // Check if the license is a string, multiple licenses are allowed if separated by commas
        if (is_string($licenses)) {
            $licenses = explode(',', $licenses);
        }

        // Check if the license is an array
        if (!is_array($licenses)) return;

        $licenses = array_map('sanitize_text_field', $licenses);

        foreach ($licenses as $license) {
            // Check if the license is stored
            $isStored = LicenseHelper::getLicenseInfo($license);

            // If the license is stored, skip validation
            if ($isStored) continue;

            try {
                $this->apiCommunicator->validateLicense($license);
            } catch (LicenseException $e) {
                Notice::addNotice(sprintf(esc_html__('Failed to validate license: %s', 'wp-statistics'), $e->getMessage()), 'license_validation', 'error');
            }
        }
    }

    public function addMenuItem($items)
    {
        $items['plugins'] = [
            'sub'      => 'overview',
            'title'    => __('Add-ons', 'wp-statistics'),
            'name'     => '<span class="wps-text-warning">' . __('Add-ons', 'wp-statistics') . '</span>',
            'page_url' => 'plugins',
            'callback' => LicenseManagerPage::class,
            'cap'      => User::ExistCapability(Option::get('manage_capability', 'manage_options')),
            'priority' => 90
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
