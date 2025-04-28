<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;

class DeviceDecorator
{
    private $visitor;

    public function __construct($visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * Get the device type (e.g., smartphone, desktop) used by the visitor.
     *
     * @return string|null
     */
    public function getType()
    {
        return \WP_STATISTICS\Admin_Template::unknownToNotSet($this->visitor->device) ?? null;
    }

    /**
     * Get the device model (e.g., iPhone, Galaxy S10) used by the visitor.
     *
     * @return string|null
     */
    public function getModel()
    {
        if (! \WP_STATISTICS\Admin_Template::isUnknown($this->visitor->model)) {
            return $this->visitor->model;
        }

        return esc_html__( 'Unknown', 'wp-statistics');
    }

    /**
     * Get the device logo URL based on the visitor's platform.
     *
     * @return string
     */
    public function getLogo()
    {
        return DeviceHelper::getDeviceLogo($this->visitor->model);
    }
}
