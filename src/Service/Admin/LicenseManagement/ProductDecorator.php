<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

class ProductDecorator
{
    private $product;
    private $licensedProduct;

    public function __construct($product, $licensedProduct = null)
    {
        $this->product = $product;
        $this->licensedProduct = $licensedProduct;
    }

    public function getVersion()
    {
        return $this->product['version'];
    }

    public function getChangelog()
    {
        return $this->product['changelog'];
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
        return 'not_in_license';
    }

    public function isActivated()
    {
        return !empty($this->licensedProduct) && $this->licensedProduct['is_active'] ?? false;
    }

    public function isInstalled()
    {
        $pluginFile = $this->getPluginFile();
        return is_plugin_active($pluginFile);
    }

    public function getPluginFile()
    {
        return $this->product['slug'] . '/' . $this->product['slug'] . '.php';
    }

    public function getDownloadUrl()
    {
        return $this->licensedProduct['download_url'] ?? null;
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
            $licensedProduct = self::findLicensedProduct($product['slug'], $licensedProducts);
            $decorated[] = new self($product, $licensedProduct);
        }
        return $decorated;
    }

    private static function findLicensedProduct($slug, array $licensedProducts)
    {
        foreach ($licensedProducts as $licensedProduct) {
            if ($licensedProduct['slug'] === $slug) {
                return $licensedProduct;
            }
        }
        return null;
    }
}
