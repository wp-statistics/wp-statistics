<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Service\Admin\LicenseManagement\ApiHandler\LicenseManagerApiFactory;

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
        try {
            // Get add-ons list
            $addOnsList = LicenseManagerApiFactory::getAddOnsList();
        } catch (\Exception $e) {
            return [
                'addons' => [],
                'error'  => $e->getMessage(),
            ];
        }

        // Add more info to response
        $response = [];
        foreach ($addOnsList as $slug => $addOn) {
            $response[$slug] = [
                'addOnObject'   => $addOn,
                'id'            => $addOn->getId(),
                'name'          => $addOn->getName(),
                'url'           => $addOn->getUrl(),
                'description'   => $addOn->getDescription(),
                'icon'          => $addOn->getIcon(),
                'version'       => $addOn->getVersion(),
                'price'         => $addOn->getPrice(),
                'featuredLabel' => $addOn->getFeaturedLabel(),
            ];

            // Add info about plugin's install or active status
            try {
                $pluginHandler = new PluginHandler($slug);

                $response[$slug]['isInstalled'] = !empty($pluginHandler->getPluginFile());
                $response[$slug]['isActive']    = $pluginHandler->isPluginActive();
            } catch (\Exception $e) {
                $response[$slug]['isInstalled'] = false;
                $response[$slug]['isActive']    = false;
            }
        }

        return [
            'addons' => $response,
            'error'  => '',
        ];
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
