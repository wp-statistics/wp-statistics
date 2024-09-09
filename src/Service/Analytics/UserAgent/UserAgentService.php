<?php

namespace WP_Statistics\Service\Analytics\UserAgent;

use DeviceDetector\DeviceDetector;

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
            $user_agent = UserAgent::getHttpUserAgent();

            // Initialize DeviceDetector with the user agent string
            $this->deviceDetector = new DeviceDetector($user_agent);
            $this->deviceDetector->parse();

        } catch (\Exception $e) {
            // In case of an error, set deviceDetector to null
            $this->deviceDetector = null;
        }
    }

    /**
     * Return the DeviceDetector instance.
     *
     * @return DeviceDetector|null
     */
    public function getDeviceDetector(): ?DeviceDetector
    {
        return $this->deviceDetector;
    }

    /**
     * Get the browser name.
     *
     * @return string|null
     */
    public function getBrowser(): ?string
    {
        return $this->deviceDetector ? $this->deviceDetector->getClient('name') : null;
    }

    /**
     * Get the platform (Operating System).
     *
     * @return string|null
     */
    public function getPlatform(): ?string
    {
        return $this->deviceDetector ? $this->deviceDetector->getOs('name') : null;
    }

    /**
     * Get the browser version.
     *
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->deviceDetector ? Helper::makeAnonymousVersion($this->deviceDetector->getClient('version')) : null;
    }

    /**
     * Get the device type (mobile/desktop).
     *
     * @return string|null
     */
    public function getDevice(): ?string
    {
        return $this->deviceDetector ? $this->deviceDetector->getDeviceName() : null;
    }

    /**
     * Get the device model.
     *
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->deviceDetector ? $this->deviceDetector->getBrandName() : null;
    }

    /**
     * Check if the browser was detected.
     *
     * @return bool
     */
    public function isBrowserDetected(): bool
    {
        return $this->deviceDetector && $this->deviceDetector->getClient('name') !== null;
    }

    /**
     * Check if the platform was detected.
     *
     * @return bool
     */
    public function isPlatformDetected(): bool
    {
        return $this->deviceDetector && $this->deviceDetector->getOs('name') !== null;
    }

    /**
     * Check if the user agent is a bot.
     *
     * @return bool
     */
    public function isBot(): bool
    {
        return $this->deviceDetector && $this->deviceDetector->isBot();
    }
}
