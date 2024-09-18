<?php

namespace WP_Statistics\Service\Analytics\Decorators;

use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;

class BrowserDecorator
{
    private $visitor;

    public function __construct($visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * Returns the raw agent value.
     *
     * @return string
     */
    public function getRaw()
    {
        return $this->visitor->agent ?? null;
    }

    /**
     * Get the browser name.
     *
     * @return string
     */
    public function getName()
    {
        return DeviceHelper::getBrowserList($this->visitor->agent);
    }

    /**
     * Get the browser logo URL.
     *
     * @return string
     */
    public function getLogo()
    {
        return DeviceHelper::getBrowserLogo($this->visitor->agent);
    }

    /**
     * Get the browser version used by the visitor.
     *
     * @return string|null
     */
    public function getVersion()
    {
        return $this->visitor->version ?? null;
    }
}
