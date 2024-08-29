<?php

namespace WP_Statistics\Service\Analytics;

use WP_STATISTICS\Helper;
use WP_STATISTICS\IP;
use WP_STATISTICS\Option;
use WP_STATISTICS\Pages;
use WP_STATISTICS\Referred;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_STATISTICS\User;
use WP_STATISTICS\UserAgent;
use WP_STATISTICS\Visitor;

class VisitorProfile
{
    private $ip;
    private $processedIPForStorage;
    private $isIpActiveToday;
    private $referrer;
    private $location;
    private $userAgent;
    private $httpUserAgent;
    private $userId;
    private $currentPageType;
    private $requestUri;

    public function __construct()
    {
    }

    /**
     * Magic method to set properties dynamically.
     *
     * @param string $name The name of the property.
     * @param mixed $value The value to set.
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    public function getIp()
    {
        if (!$this->ip) {
            $this->ip = IP::getIP();
        }

        return $this->ip;
    }

    public function getProcessedIPForStorage()
    {
        if (!$this->processedIPForStorage) {
            $this->processedIPForStorage = IP::getStoreIP();
        }

        return $this->processedIPForStorage;
    }

    public function isIpActiveToday()
    {
        if (!$this->isIpActiveToday) {
            $this->isIpActiveToday = Visitor::exist_ip_in_day($this->getProcessedIPForStorage());
        }

        return $this->isIpActiveToday;
    }

    /**
     * Get the location of the visitor.
     *
     * @return array
     */
    public function getLocation($location = null)
    {
        if (!$this->location) {
            $this->location = GeolocationFactory::getLocation($this->getIp());
        }

        if ($location) {
            return $this->location[$location];
        }

        return $this->location;
    }

    public function getCountry()
    {
        return $this->getLocation('country');
    }

    public function getCity()
    {
        return $this->getLocation('city');
    }

    public function getRegion()
    {
        return $this->getLocation('region');
    }

    public function getContinent()
    {
        return $this->getLocation('continent');
    }

    public function getLatitude()
    {
        return $this->getLocation('latitude');
    }

    public function getLongitude()
    {
        return $this->getLocation('longitude');
    }

    public function getReferrer()
    {
        if (!$this->referrer) {
            $this->referrer = Referred::get();
        }

        return $this->referrer;
    }

    public function getUserAgent()
    {
        if (!$this->userAgent) {
            $this->userAgent = UserAgent::getUserAgent();
        }

        return $this->userAgent;
    }

    public function getHttpUserAgent()
    {
        if (!$this->httpUserAgent) {
            $this->httpUserAgent = UserAgent::getHttpUserAgent();
        }

        return $this->httpUserAgent;
    }

    public function getRequestUri()
    {
        if (!$this->requestUri) {
            $this->requestUri = Helper::getRequestUri();
        }

        return $this->requestUri;
    }

    public function getUserId()
    {
        if (!$this->userId) {
            if (!Option::get('visitors_log') || Helper::shouldTrackAnonymously()) {
                $this->userId = 0;
            } else {
                $this->userId = User::get_user_id();
            }
        }

        return $this->userId;
    }

    public function getCurrentPageType()
    {
        if (!$this->currentPageType) {
            $this->currentPageType = Pages::get_page_type();
        }

        return $this->currentPageType;
    }
}