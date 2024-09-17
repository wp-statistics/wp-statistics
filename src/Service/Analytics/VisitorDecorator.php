<?php

namespace WP_Statistics\Service\Analytics;

use WP_STATISTICS\Country;
use WP_STATISTICS\IP;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Analytics\Referrals\ReferralDecorator;
use WP_STATISTICS\User;
use WP_STATISTICS\Visitor;

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
     * Get the country icon URL based on the visitor's location.
     *
     * @return string
     */
    public function getCountryFlag()
    {
        return Country::flag($this->visitor->location);
    }

    /**
     * Get the country name based on the visitor's location.
     *
     * @return string
     */
    public function getCountryName()
    {
        return Country::getName($this->visitor->location);
    }

    /**
     * Retrieves the country code of the visitor.
     *
     * @return string|null The country code, or null if not available.
     */
    public function getCountryCode()
    {
        return $this->visitor->location ?? null;
    }

    /**
     * Get the visitor's agent.
     *
     * @return string|null
     */
    public function getBrowser()
    {
        return $this->visitor->agent ?? null;
    }

    /**
     * Get the browser logo URL based on the visitor's browser.
     *
     * @return string
     */
    public function getBrowserName()
    {
        return DeviceHelper::getBrowserList($this->visitor->agent);
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
     * Get the platform (operating system)
     *
     * @return string
     */
    public function getOs()
    {
        return $this->visitor->platform ?? null;
    }

    /**
     * Get the platform (operating system) logo URL based on the visitor's platform.
     *
     * @return string
     */
    public function getOsLogo()
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
        return $this->isHashedIP() ? substr($this->visitor->ip, 6, 10) : $this->visitor->ip;
    }

    /**
     * Is the visitor's IP hashed?
     *
     * @return string|null
     */
    public function isHashedIP()
    {
        return IP::IsHashIP($this->visitor->ip);
    }

    /**
     * Retrieves the region of the visitor.
     *
     * @return string|null The region of the visitor, or null if not available.
     */
    public function getRegion()
    {
        return $this->visitor->region ?? null;
    }

    /**
     * Retrieves the city associated with the visitor's location.
     *
     * @return string|null The city name, or null if not available.
     */
    public function getCity()
    {
        return $this->visitor->city ?? null;
    }

    /**
     * Retrieves the continent associated with the visitor's location.
     *
     * @return string|null The continent name, or null if not available.
     */
    public function getContinent()
    {
        return $this->visitor->continent ?? null;
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
     * Get the last counter value recorded for the visitor.
     *
     * @return int|null
     */
    public function getLastCounter()
    {
        return $this->visitor->last_counter ?? null;
    }

    /**
     * Get the visitor's referral information.
     *
     * @return ReferralDecorator
     */
    public function getReferral()
    {
        return new ReferralDecorator($this->visitor);
    }

    /**
     * Get the visitor's ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->visitor->ID ?? null;
    }


    /**
     * Checks whether the visitor is a logged-in user.
     *
     * @return bool True if the visitor is logged in, false otherwise.
     */
    public function isLoggedInUser()
    {
        return !empty($this->visitor->user_id);
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

    /**
     * Retrieves the username of the visitor.
     *
     * @return string|null The username of the visitor, or null if not available.
     */
    public function getUserName()
    {
        return $this->visitor->display_name ?? null;
    }

    /**
     * Retrieves the email address of the visitor if they are a logged-in user.
     *
     * @return string|null The visitor's email address, or null if not available.
     */
    public function getUserEmail()
    {
        return $this->visitor->user_email ?? null;
    }

    /**
     * Retrieves the first role of the visitor.
     *
     * @return string|null The visitor's first role, or null if not available.
     */
    public function getUserRole()
    {
        return User::get($this->visitor->user_id)['role'][0] ?? null;
    }

    /**
     * Retrieves the first view time of the visitor.
     *
     * @return int|null The time of the first view, or null if not available.
     */
    public function getFirstView()
    {
        return $this->visitor->first_view ?? null;
    }

    /**
     * Retrieves the first page viewed by the visitor.
     *
     * @return string|null The first page viewed by the visitor, or null if not available.
     */
    public function getFirstPage()
    {
        return $this->visitor->first_page ? Visitor::get_page_by_id($this->visitor->first_page) : null;
    }

    /**
     * Retrieves the last view time of the visitor.
     *
     * @return int|null The time of the last view, or null if not available.
     */
    public function getLastView()
    {
        return $this->visitor->last_view ?? null;
    }

    /**
     * Retrieves the last page viewed by the visitor.
     *
     * @return string|null The last page viewed by the visitor, or null if not available.
     */
    public function getLastPage()
    {
        return $this->visitor->last_page ? Visitor::get_page_by_id($this->visitor->last_page) : null;
    }
}
