<?php

namespace WP_Statistics\Decorators;

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
        return \WP_STATISTICS\Admin_Template::unknownToNotSet($this->visitor->model) ?? null;
    }
}
