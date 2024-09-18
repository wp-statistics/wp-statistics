<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

class ProductDecorator
{
    private $product;
    private $licensedProduct;
    private $pluginHandler;

    public function __construct($product = null, $licensedProduct = null)
    {
        $this->product         = $product;
        $this->licensedProduct = $licensedProduct;

        $this->pluginHandler   = new PluginHandler();
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

    public function getStatus()
    {
        if ($this->licensedProduct) {
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
            $licensedProduct = self::findLicensedProduct($product->slug, $licensedProducts);
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
}
