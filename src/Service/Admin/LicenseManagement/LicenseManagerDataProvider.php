<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

class LicenseManagerDataProvider
{
    protected $args;

    /** @var LicenseValidator */
    protected $licenseValidator;

    public function __construct($args = [])
    {
        $this->args = $args;

        $this->licenseValidator = new LicenseValidator();
    }

    /**
     * Returns data for "Add-Ons" tab.
     *
     * @return array
     */
    public function getAddOnsData()
    {
        return '';
    }

    /**
     * Returns data for "Add Your License" tab.
     *
     * @return array
     */
    public function getAddLicenseData()
    {
        return '';
    }

    /**
     * Returns data for "Download Add-ons" tab.
     *
     * @return array
     */
    public function getDownloadsData()
    {
        return '';
    }

    /**
     * Returns data for "Get Started" tab.
     *
     * @return array
     */
    public function getGetStartedData()
    {
        return '';
    }
}
