<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;

class OsDecorator
{
    private $visitor;

    public function __construct($visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * Get the operating system name
     *
     * @return string
     */
    public function getName()
    {
        return \WP_STATISTICS\Admin_Template::unknownToNotSet($this->visitor->platform) ?? null;
    }

    /**
     * Get the operating system logo URL based on the visitor's platform.
     *
     * @return string
     */
    public function getLogo()
    {
        return DeviceHelper::getPlatformLogo($this->visitor->platform);
    }
}
