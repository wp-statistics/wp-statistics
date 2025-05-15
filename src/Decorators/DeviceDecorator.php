<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_STATISTICS\Admin_Template;

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
        $device = $this->visitor->device ? ucfirst($this->visitor->device) : null;
        return \WP_STATISTICS\Admin_Template::unknownToNotSet($device);
    }

    /**
     * Get the device model (e.g., iPhone, Galaxy S10) used by the visitor.
     *
     * @return string|null
     */
    public function getModel()
    {
        return \WP_STATISTICS\Admin_Template::unknownToNotSet($this->visitor->model);
    }

    /**
     * Get the device logo URL based on the visitor's platform.
     *
     * @return string
     */
    public function getLogo()
    {
        return DeviceHelper::getDeviceLogo($this->visitor->device ?? '');
    }
}
