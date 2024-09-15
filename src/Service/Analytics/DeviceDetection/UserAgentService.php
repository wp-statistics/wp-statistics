<?php

namespace WP_Statistics\Service\Analytics\DeviceDetection;

use DeviceDetector\DeviceDetector;
use Exception;
use WP_STATISTICS\Helper;

class UserAgentService
{
    protected $deviceDetector;

    /**
     * Constructor to initialize DeviceDetector with the user agent.
     */
    public function __construct()
    {
        try {
            // Get HTTP User Agent
            $userAgent = UserAgent::getHttpUserAgent();

            // Initialize DeviceDetector with the user agent string
            $this->deviceDetector = new \WP_Statistics\Dependencies\DeviceDetector\DeviceDetector($userAgent);
            $this->deviceDetector->parse();

        } catch (Exception $e) {
            // In case of an error, set deviceDetector to null
            $this->deviceDetector = null;
        }
    }

    /**
     * Return the DeviceDetector instance.
     *
     * @return DeviceDetector|null
     */
    public function getDeviceDetector()
    {
        return $this->deviceDetector;
    }

    /**
     * Get the browser name.
     *
     * @return string|null
     */
    public function getBrowser()
    {
        return $this->deviceDetector ? $this->deviceDetector->getClient('name') : null;
    }

    /**
     * Get the platform (Operating System).
     *
     * @return string|null
     */
    public function getPlatform()
    {
        return $this->deviceDetector ? $this->deviceDetector->getOs('name') : null;
    }

    /**
     * Get the browser version.
     *
     * @return string|null
     */
    public function getVersion()
    {
        return $this->deviceDetector ? Helper::makeAnonymousVersion($this->deviceDetector->getClient('version')) : null;
    }

    /**
     * Get the device type (mobile/desktop).
     *
     * @return string|null
     */
    public function getDevice()
    {
        return $this->deviceDetector ? $this->deviceDetector->getDeviceName() : null;
    }

    /**
     * Get the device model.
     *
     * @return string|null
     */
    public function getModel()
    {
        return $this->deviceDetector ? $this->deviceDetector->getBrandName() : null;
    }

    /**
     * Check if the browser was detected.
     *
     * @return bool
     */
    public function isBrowserDetected()
    {
        return $this->deviceDetector && $this->deviceDetector->getClient('name') !== null;
    }

    /**
     * Check if the platform was detected.
     *
     * @return bool
     */
    public function isPlatformDetected()
    {
        return $this->deviceDetector && $this->deviceDetector->getOs('name') !== null;
    }

    /**
     * Check if the user agent is a bot.
     *
     * @return bool
     */
    public function isBot()
    {
        return $this->deviceDetector && $this->deviceDetector->isBot();
    }
}
