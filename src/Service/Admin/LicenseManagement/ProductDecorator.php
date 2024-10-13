<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

class ProductDecorator
{
    private $product;
    private $licensedProduct;
    private $pluginHandler;

    /**
     * @param ProductDecorator|object $product
     * @param object $licensedProduct
     */
    public function __construct($product = null, $licensedProduct = null)
    {
        if ($product instanceof ProductDecorator) {
            $this->product = $product->getProductObject();
        } else {
            $this->product = $product;
        }
        $this->licensedProduct = $licensedProduct;
        $this->pluginHandler   = new PluginHandler();
    }

    /**
     * Returns the raw product object.
     *
     * @return object
     */
    public function getProductObject()
    {
        return $this->product;
    }

    public function getId()
    {
        return $this->product->id;
    }

    /**
     * Returns product slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->product->slug;
    }

    /**
     * Returns product name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->product->name;
    }

    /**
     * Returns product description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->product->description;
    }

    /**
     * Returns product's short description.
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->product->short_description;
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
        return $this->product->thumbnail;
    }

    /**
     * Returns product price.
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->product->price;
    }

    /**
     * Returns product label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->product->label;
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
        return $this->product->version;
    }

    public function getChangelogUrl()
    {
        return $this->product->changelog_url;
    }

    public function getChangelog()
    {
        return $this->product->changelog;
    }

    /**
     * Returns product URL.
     *
     * @return string
     */
    public function getProductUrl()
    {
        return $this->product->product_url;
    }

    /**
     * Returns product's documentation URL.
     *
     * @return string
     */
    public function getDocumentationUrl()
    {
        return $this->product->documentation_url;
    }

    /**
     * Does this product have a license?
     *
     * @return bool
     */
    public function isLicensed()
    {
        return !empty($this->licensedProduct);
    }

    public function getStatus()
    {
        if (!$this->isInstalled()) {
            return 'not_installed';
        } else if (!$this->isActivated()) {
            return 'not_activated'; // same as 'installed'
        } else if (!$this->isLicensed()) {
            return 'not_licensed';
        } else {
            return 'activated';
        }
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
            case 'not_activated':
                return __('Inactive', 'wp-statistics');
            case 'not_licensed':
                return __('Needs License', 'wp-statistics');
            case 'activated':
                return __('Activated', 'wp-statistics');
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
                return 'danger';
            case 'activated':
                return 'success';
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
        return $this->licensedProduct->download_url ?? null;
    }

    public static function decorateProducts($products)
    {
        $decorated = [];
        foreach ($products as $product) {
            $decorated[] = new self($product);
        }
        return $decorated;
    }

    public static function decorateProductsWithLicense(array $products, array $licensedProducts)
    {
        $decorated = [];
        foreach ($products as $product) {
            $slug = '';
            if ($product instanceof ProductDecorator) {
                $slug = $product->getSlug();
            } else {
                $slug = $product->slug;
            }

            $licensedProduct = self::findLicensedProduct($slug, $licensedProducts);
            $decorated[]     = new self($product, $licensedProduct);
        }
        return $decorated;
    }

    private static function findLicensedProduct($slug, array $licensedProducts)
    {
        foreach ($licensedProducts as $licensedProduct) {
            if ($licensedProduct->slug === $slug) {
                return $licensedProduct;
            }
        }
        return null;
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
