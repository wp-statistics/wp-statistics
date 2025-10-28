<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

use Exception;
use stdClass;
use WP_Statistics;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class PluginUpdater
 *
 * Handles updating WP Statistics add-ons by fetching the latest version information from a remote API
 * and integrating it with the WordPress plugin update system.
 */
class PluginUpdater
{
    private $pluginSlug;
    private $pluginVersion;
    private $licenseKey;
    private $pluginFilePath;

    /**
     * PluginUpdater constructor.
     * Initializes the class properties and sets up necessary WordPress hooks.
     *
     * @param string $pluginSlug
     * @param string $pluginVersion
     * @param string $licenseKey
     */
    public function __construct($pluginSlug, $pluginVersion, $licenseKey = '')
    {
        $this->pluginSlug     = $pluginSlug;
        $this->pluginVersion  = $pluginVersion;
        $this->licenseKey     = $licenseKey;
        $this->pluginFilePath = $this->pluginSlug . '/' . $this->pluginSlug . '.php';
    }

    /**
     * Hooks to check for updates and add necessary filters and actions.
     */
    public function handle()
    {
        add_filter('plugins_api', [$this, 'pluginsApiInfo'], 20, 3);
        add_filter('pre_set_site_transient_update_plugins', [$this, 'checkForUpdate']);
        add_action('upgrader_process_complete', [$this, 'clearCache'], 10, 2);
    }

    public function handleLicenseNotice()
    {
        add_action("after_plugin_row_{$this->pluginFilePath}", [$this, 'showLicenseNotice'], 10, 2);
    }

    /**
     * Handle the plugins_api call, returning version information when requested.
     *
     * @param mixed $res
     * @param string $action
     * @param object $args
     * @return mixed
     */
    public function pluginsApiInfo($res, $action, $args)
    {
        if ($action !== 'plugin_information' || $this->pluginSlug !== $args->slug) {
            return $res;
        }

        $remote = $this->requestUpdateInfo();

        if (!$remote) {
            return $res;
        }

        $res                 = new stdClass();
        $res->name           = $remote->name;
        $res->slug           = $remote->slug;
        $res->version        = $remote->version;
        $res->tested         = $remote->tested;
        $res->requires       = $remote->requires;
        $res->author         = $remote->author;
        $res->author_profile = $remote->author_profile;
        $res->download_link  = $remote->download_url;
        $res->requires_php   = $remote->requires_php;
        $res->last_updated   = $remote->last_updated;

        // Sections such as description, installation, changelog
        $res->sections = [
            'description'  => $remote->sections->description,
            'installation' => $remote->sections->installation,
            'changelog'    => $remote->sections->changelog,
        ];

        $res->icons = [
            '1x' => $remote->icons->low,
            '2x' => $remote->icons->high,
        ];

        $res->banners = [
            'low'  => $remote->banners->low,
            'high' => $remote->banners->high,
        ];

        return $res;
    }

    /**
     * Fetch the version information from the API using ApiCommunicator and handle exceptions.
     *
     * @return object|false
     */
    private function requestUpdateInfo()
    {
        // Don't request update info if the license key is not provided
        if (empty($this->licenseKey)) {
            return false;
        }

        try {
            $apiCommunicator = new ApiCommunicator();
            $remote          = $apiCommunicator->getDownloadUrl($this->licenseKey, $this->pluginSlug);

            if (!$remote) {
                throw new Exception('Failed to retrieve remote plugin information.');
            }

            if (isset($remote->code) && isset($remote->message)) {
                throw new Exception($remote->message, $remote->code);
            }

            /**
             * Adjusts the tested version to ignore the patch number for compatibility checks.
             * Based on the patch from https://core.trac.wordpress.org/ticket/62151.
             *
             * @note If the patch is applied in WordPress core, this section can be removed.
             */
            if (isset($remote->tested)) {
                $remote->tested = $this->adjustPatchVersion($remote->tested);
            }

            return $remote;

        } catch (Exception $e) {
            WP_Statistics::log($e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Check if an update is available by comparing versions.
     *
     * @param object $transient
     * @return object
     */
    public function checkForUpdate($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = $this->requestUpdateInfo();

        if ($remote) {
            $res              = new stdClass();
            $res->slug        = $this->pluginSlug;
            $res->plugin      = $this->pluginFilePath;
            $res->new_version = $remote->version;
            $res->tested      = $remote->tested;
            $res->package     = $remote->download_url;
            $res->icons       = [
                '1x' => $remote->icons->low,
                '2x' => $remote->icons->high,
            ];
            $res->banners     = [
                'low'  => $remote->banners->low,
                'high' => $remote->banners->high,
            ];

            if (version_compare($this->pluginVersion, $remote->version, '<')) {
                $transient->response[$res->plugin] = $res;
            } else {
                $transient->no_update[$res->plugin] = $res;
            }
        }

        return $transient;
    }

    /**
     * Show a notice if the license key is missing or the update request fails.
     *
     * @param string $pluginFile
     * @param array $pluginData
     */
    public function showLicenseNotice($pluginFile, $pluginData)
    {
        // Get the columns for this table so we can calculate the colspan attribute.
        $screen  = get_current_screen();
        $columns = get_column_headers($screen);

        // If something went wrong with retrieving the columns, default to 3 for colspan.
        $colspan = !is_countable($columns) ? 3 : count($columns);

        $isActive = is_plugin_active($this->pluginFilePath);

        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        ?>
        <tr class='license-error-tr plugin-update-tr update <?php echo $isActive ? 'active' : ''; ?>' data-plugin='<?php echo esc_attr($this->pluginFilePath); ?>' data-plugin-row-type='feature-incomp-warn'>
            <td colspan='<?php echo esc_attr($colspan); ?>' class='plugin-update'>
                <div class='notice inline notice-warning notice-alt'>
                    <p>
                        <?php echo sprintf(__('Automatic updates are disabled for the <b>%s</b>.', 'wp-statistics'), esc_attr($pluginData['Name'])); ?>
                        <?php echo sprintf(__('To unlock automatic updates and access new features and security improvements, please <a href="%s">activate your license</a>.', 'wp-statistics'), Menus::admin_url('plugins', ['tab' => 'add-license'])); ?>
                    </p>
                </div>
            </td>
        </tr>
        <?php
        // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Clear cache after the plugin upgrade process completes.
     *
     * @param \WP_Upgrader $upgrader
     * @param array $options
     */
    public function clearCache($upgrader, $options)
    {
        if ('update' === $options['action'] && 'plugin' === $options['type']) {
            //delete_transient(); // todo
        }
    }

    /**
     * Adjusts the patch version of the plugin to match the current WordPress version.
     *
     * @param string $testedVersion The version retrieved from the API.
     * @return string The adjusted version.
     */
    public function adjustPatchVersion($testedVersion)
    {
        global $wp_version;

        // Adjust the tested version to the same patch level as the current WordPress version
        $testedParts = explode('.', $testedVersion);
        $wpParts     = explode('.', $wp_version);

        // Ensure both versions have at least two parts
        if (count($testedParts) >= 2 && count($wpParts) >= 2) {
            return $testedParts[0] . '.' . $testedParts[1] . '.' . ($wpParts[2] ?? '0');
        }

        return $testedVersion;
    }
}
