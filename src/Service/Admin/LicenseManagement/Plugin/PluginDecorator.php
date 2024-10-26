<?php
namespace WP_Statistics\Service\Admin\LicenseManagement\Plugin;

use Exception;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

class PluginDecorator
{
    private $plugin;
    private $pluginHandler;
    private $apiCommunicator;

    public function __construct($plugin)
    {
        $this->apiCommunicator  = new ApiCommunicator();
        $this->pluginHandler    = new PluginHandler();
        $this->plugin           = $plugin;
    }

    public function getId()
    {
        return $this->plugin->id;
    }

    /**
     * Returns product slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->plugin->slug;
    }

    /**
     * Returns product name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->plugin->name;
    }

    /**
     * Returns product description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->plugin->description;
    }

    /**
     * Returns product's short description.
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->plugin->short_description;
    }

    /**
     * Returns product icon `src` to use in `img` tag.
     *
     * @return string
     */
    public function getIcon()
    {
        $iconPath = "assets/images/add-ons/{$this->getSlug()}.svg";
        if (file_exists(WP_STATISTICS_DIR . $iconPath)) {
            return esc_url(WP_STATISTICS_URL . $iconPath);
        }

        return $this->getThumbnail();
    }

    /**
     * Returns product thumbnail URL.
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this->plugin->thumbnail;
    }

    /**
     * Returns product price.
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->plugin->price;
    }

    /**
     * Returns product label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->plugin->label;
    }

    /**
     * Returns label CSS class that will be used in the front-end.
     *
     * @return string
     */
    public function getLabelClass()
    {
        if (stripos($this->getLabel(), 'new') !== false) {
            return 'new';
        }

        if (stripos($this->getLabel(), 'updated') !== false) {
            return 'updated';
        }

        return 'updated';
    }

    public function getVersion()
    {
        return $this->plugin->version;
    }

    public function getChangelogUrl()
    {
        return $this->plugin->changelog_url;
    }

    public function getChangelog()
    {
        return $this->plugin->changelog;
    }

    /**
     * Returns product URL.
     *
     * @return string
     */
    public function getProductUrl()
    {
        return $this->plugin->product_url;
    }

    /**
     * Returns product's documentation URL.
     *
     * @return string
     */
    public function getDocumentationUrl()
    {
        return $this->plugin->documentation_url;
    }

    /**
     * Does this product have a valid license?
     *
     * @return bool
     */
    public function isLicenseValid()
    {
        return LicenseHelper::isPluginLicenseValid($this->getSlug());
    }

    /**
     * Check if the license for this product is expired.
     */
    public function isLicenseExpired()
    {
        return LicenseHelper::isPluginLicenseExpired($this->getSlug());
    }

    public function getStatus()
    {
        if (!$this->isInstalled()) {
            return 'not_installed';
        }

        if ($this->isLicenseExpired()) {
            return 'license_expired';
        }

        if (!$this->isLicenseValid()) {
            return 'not_licensed';
        }

        if (!$this->isActivated()) {
            return 'not_activated'; // same as 'installed'
        }

        return 'activated';
    }

    /**
     * Returns status label that will be printed in the front-end.
     *
     * @return string
     */
    public function getStatusLabel()
    {
        switch ($this->getStatus()) {
            case 'not_installed':
                return __('Not Installed', 'wp-statistics');
            case 'not_licensed':
                return __('Needs License', 'wp-statistics');
            case 'license_expired':
                return __('License Expired', 'wp-statistics');
            case 'not_activated':
                return __('Inactive', 'wp-statistics');
            case 'activated':
                return __('Activated', 'wp-statistics');
            default:
                return __('Unknown', 'wp-statistics');
        }
    }

    /**
     * Returns status CSS class that will be used in the front-end.
     *
     * @return string
     */
    public function getStatusClass()
    {
        switch ($this->getStatus()) {
            case 'not_installed':
                return 'disable';
            case 'not_activated':
                return 'primary';
            case 'not_licensed':
            case 'license_expired':
                return 'danger';
            case 'activated':
                return 'success';
            default:
                throw new Exception('Unknown status');
        }
    }

    public function isInstalled()
    {
        return $this->pluginHandler->isPluginInstalled($this->getSlug());
    }

    public function isActivated()
    {
        return $this->pluginHandler->isPluginActive($this->getSlug());
    }

    public function getDownloadUrl()
    {
        $downloadUrl = $this->apiCommunicator->getDownloadUrlFromLicense($this->getLicenseKey(), $this->getSlug());
        return $downloadUrl ?? null;
    }

    public function getLicenseKey()
    {
        return LicenseHelper::getPluginLicense($this->getSlug());
    }

    /**
     * Returns add-on's settings page link.
     *
     * @return string Settings URL.
     */
    public function getSettingsUrl()
    {
        $pluginName = str_replace('wp-statistics-', '', $this->getSlug());
        $tab        = !empty($pluginName) ? "$pluginName-settings" : '';

        return esc_url(Menus::admin_url('settings', ['tab' => $tab]));
    }

    /**
     * Compares add-on version in license status call with the version of the locally installed plugin.
     *
     * @return bool|null `null` on error or if the plugin is not installed.
     */
    public function isUpdateAvailable()
    {
        if (!$this->isInstalled()) {
            return null;
        }

        $installedPlugin = null;
        try {
            $installedPlugin = $this->pluginHandler->getPluginData($this->getSlug());

            // If there's an issue with local plugin's version, return true so that a new version can be downloaded
            if (empty($installedPlugin) || empty($installedPlugin['Version'])) {
                return true;
            }
        } catch (\Exception $e) {
            return null;
        }

        return version_compare($this->getVersion(), $installedPlugin['Version'], '>');
    }
}