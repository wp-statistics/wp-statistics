<?php

namespace WP_Statistics\Service\Analytics;

use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;

class VisitorDecorator
{
    /**
     * @var mixed
     */
    private $visitor;

    /**
     * VisitorDecorator constructor.
     * @param mixed $visitor
     */
    public function __construct($visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * Get the browser logo URL based on the visitor's browser.
     *
     * @return string
     */
    public function getBrowserLogo()
    {
        return DeviceHelper::getBrowserLogo($this->visitor->agent);
    }

    /**
     * Get the platform (operating system) logo URL based on the visitor's platform.
     *
     * @return string
     */
    public function getPlatformLogo()
    {
        return DeviceHelper::getPlatformLogo($this->visitor->platform);
    }

    /**
     * Get the browser version used by the visitor.
     *
     * @return string|null
     */
    public function getBrowserVersion()
    {
        return $this->visitor->version ?? null;
    }

    /**
     * Get the device name (e.g., smartphone, desktop) used by the visitor.
     *
     * @return string|null
     */
    public function getDevice()
    {
        return $this->visitor->device ?? null;
    }

    /**
     * Get the device model (e.g., iPhone, Galaxy S10) used by the visitor.
     *
     * @return string|null
     */
    public function getDeviceModel()
    {
        return $this->visitor->model ?? null;
    }

    /**
     * Get the visitor's user agent string.
     *
     * @return string|null
     */
    public function getUserAgent()
    {
        return $this->visitor->UAString ?? null;
    }

    /**
     * Get the visitor's IP address.
     *
     * @return string|null
     */
    public function getIP()
    {
        return $this->visitor->ip ?? null;
    }

    /**
     * Get the location (city, region, continent) of the visitor.
     *
     * @return array
     */
    public function getLocation()
    {
        return [
            'city'      => $this->visitor->city ?? null,
            'region'    => $this->visitor->region ?? null,
            'continent' => $this->visitor->continent ?? null,
        ];
    }

    /**
     * Get the number of hits the visitor has made.
     *
     * @return int|null
     */
    public function getHits()
    {
        return $this->visitor->hits ?? null;
    }

    /**
     * Get the honeypot status (whether the visitor triggered honeypot protection).
     *
     * @return bool|null
     * @deprecated This will probably depreciate in v15
     */
    public function isHoneypotTriggered()
    {
        return $this->visitor->honeypot ?? null;
    }

    /**
     * Get the visitor's source channel (e.g., direct, referral, etc.).
     *
     * @return string|null
     */
    public function getSourceChannel()
    {
        return $this->visitor->source_channel ?? null;
    }

    /**
     * Get the last counter value recorded for the visitor.
     *
     * @return int|null
     */
    public function getLastCounter()
    {
        return $this->visitor->last_counter ?? null;
    }

    /**
     * Get the referred URL of the visitor (if available).
     *
     * @return string|null
     */
    public function getReferred()
    {
        return $this->visitor->referred ?? null;
    }

    /**
     * Get the visitor's user ID (if logged in).
     *
     * @return int|null
     */
    public function getUserId()
    {
        return $this->visitor->user_id ?? null;
    }
}
