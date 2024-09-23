<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_STATISTICS\Menus;

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
        }

        if ($this->isLicensed()) {
            if ($this->isActivated()) {
                return 'activated';
            } elseif ($this->isInstalled()) {
                return 'installed';
            } else {
                return 'not_activated';
            }
        }
        return 'not_licensed';
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
            case 'installed':
                return __('Installed', 'wp-statistics');
            case 'activated':
                return __('Activated', 'wp-statistics');
        }

        return __('Needs License', 'wp-statistics');
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
            case 'installed':
                return 'primary';
            case 'activated':
                return 'success';
        }

        return 'danger';
    }

    public function isActivated()
    {
        try {
            return $this->pluginHandler->isPluginActive($this->product->slug);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isInstalled()
    {
        return !empty($this->getPluginFile());
    }

    public function getPluginFile()
    {
        $pluginFile = null;
        try {
            $pluginFile = $this->pluginHandler->getPluginFile($this->product->slug);
        } catch (\Exception $e) {
            return null;
        }

        return $pluginFile;
    }

    public function getDownloadUrl()
    {
        return $this->licensedProduct->download_url ?? null;
    }

    public static function decorateProducts(array $products)
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
        $tab = '';
        switch ($this->getSlug()) {
            case 'wp-statistics-data-plus':
                $tab = 'data-plus-settings';
                break;
            case 'wp-statistics-realtime-stats':
                $tab = 'realtime-stats-settings';
                break;
            case 'wp-statistics-customization':
                $tab = 'customization-settings';
                break;
            case 'wp-statistics-advanced-reporting':
                $tab = 'advanced-reporting-settings';
                break;
            case 'wp-statistics-mini-chart':
                $tab = 'mini-chart-settings';
                break;
            case 'wp-statistics-rest-api':
                $tab = 'rest-api-settings';
                break;
            case 'wp-statistics-widgets':
                $tab = 'widgets-settings';
                break;
        }

        $args = [];
        if (!empty($tab)) {
            $args['tab'] = $tab;
        }

        return esc_url(Menus::admin_url('settings', $args));
    }
}
