<?php

namespace WP_Statistics\Service\Analytics;

use WP_STATISTICS\GeoIP;
use WP_STATISTICS\Helper;
use WP_STATISTICS\IP;
use WP_STATISTICS\Pages;
use WP_STATISTICS\Referred;
use WP_STATISTICS\User;
use WP_STATISTICS\UserAgent;
use WP_STATISTICS\Visitor;

class VisitorProfile
{
    private $ip;
    private $processedIPForStorage;
    private $isIpActiveToday;
    private $referrer;
    private $country;
    private $city;
    private $userAgent;
    private $httpUserAgent;
    private $userId;
    private $currentPageType;
    private $requestUri;

    public function __construct()
    {
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

    public function getCountry()
    {
        if (!$this->country) {
            $this->country = GeoIP::getCountry($this->getIp());
        }

        return $this->country;
    }

    public function getCity()
    {
        if (!$this->city) {
            $this->city = GeoIP::getCity($this->getIp(), true);
        }

        return $this->city['city'];
    }

    public function getRegion()
    {
        if (!$this->city) {
            $this->city = GeoIP::getCity($this->getIp(), true);
        }

        return $this->city['region'];
    }

    public function getContinent()
    {
        if (!$this->city) {
            $this->city = GeoIP::getCity($this->getIp(), true);
        }

        return $this->city['continent'];
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
            $this->userId = User::get_user_id();
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