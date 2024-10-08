<?php

namespace WP_Statistics\Decorators;

use WP_STATISTICS\Helper;
use WP_STATISTICS\IP;
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
     * Get the visitor's ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->visitor->ID ?? null;
    }

    /**
     * Returns the visitor's location.
     *
     * @return LocationDecorator
     */
    public function getLocation()
    {
        return new LocationDecorator($this->visitor);
    }

    /**
     * Get the visitor's browser.
     *
     * @return BrowserDecorator
     */
    public function getBrowser()
    {
        return new BrowserDecorator($this->visitor);
    }

    /**
     * Get the platform (operating system)
     *
     * @return OsDecorator
     */
    public function getOs()
    {
        return new OsDecorator($this->visitor);
    }

    /**
     * Get the device used by the visitor.
     *
     * @return DeviceDecorator
     */
    public function getDevice()
    {
        return new DeviceDecorator($this->visitor);
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
        return $this->isHashedIP() ? '#' . substr($this->visitor->ip, 6, 8) : $this->visitor->ip;
    }

    /**
     * Returns the raw IP address of the visitor.
     *
     * @return string The raw IP address of the visitor.
     */
    public function getRawIP()
    {
        return $this->visitor->ip;
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
     * Get the number of hits the visitor has made.
     *
     * @return int|null
     */
    public function getHits()
    {
        return $this->visitor->hits ? number_format_i18n(intval($this->visitor->hits)) : 0;
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
     * Checks whether the visitor is a logged-in user.
     *
     * @return bool True if the visitor is logged in, false otherwise.
     */
    public function isLoggedInUser()
    {
        return !empty($this->visitor->user_id);
    }

    /**
     * Get the visitor's user object (if logged in).
     *
     * @return UserDecorator
     */
    public function getUser()
    {
        return new UserDecorator($this->visitor);
    }

    /**
     * Retrieves the first view time of the visitor.
     *
     * @return int|null The time of the first view, or null if not available.
     */
    public function getFirstView()
    {
        return $this->visitor->first_view ? date_i18n(Helper::getDefaultDateFormat(true, true, false, ', '), strtotime($this->visitor->first_view)) : null;
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
     * @return string The time of the last view, or null if not available.
     */
    public function getLastView()
    {
        // Get date from last_view (DateTime), if not set use last_counter (Date)
        $date = $this->visitor->last_view ?? $this->visitor->last_counter;
        $date = date_i18n(Helper::getDefaultDateFormat(true, true, false, ', '), strtotime($date));

        return $date ?? null;
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

    /**
     * Retrieves the online time of the visitor.
     *
     * @return string|null The online time in 'H:i:s' format, or null if not available.
     */
    public function getOnlineTime()
    {
        if (isset($this->visitor->timestamp) && isset($this->visitor->created)) {
            return date_i18n('H:i:s', $this->visitor->timestamp - $this->visitor->created);
        }

        return null;
    }
}