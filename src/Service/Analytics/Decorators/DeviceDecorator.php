<?php

namespace WP_Statistics\Service\Analytics\Decorators;

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
        return $this->visitor->device ?? null;
    }

    /**
     * Get the device model (e.g., iPhone, Galaxy S10) used by the visitor.
     *
     * @return string|null
     */
    public function getModel()
    {
        return $this->visitor->model ?? null;
    }
}
