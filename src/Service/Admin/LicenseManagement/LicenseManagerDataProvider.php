<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Service\Admin\LicenseManagement\ApiHandler\LicenseManagerApiFactory;

class LicenseManagerDataProvider
{
    protected $args;
    private $licenseService;

    public function __construct($args = [])
    {
        $this->args           = $args;
        $this->licenseService = new LicenseManagementService();
    }

    /**
     * Return a list of the product for view
     *
     * @return ProductDecorator[]
     * @throws \Exception
     */
    public function getProductList()
    {
        return $this->licenseService->getProductList();
    }
}
