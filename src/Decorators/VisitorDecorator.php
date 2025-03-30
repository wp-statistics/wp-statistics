<?php

namespace WP_Statistics\Decorators;

use WP_STATISTICS\IP;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Visitor;
use WP_Statistics\Components\DateTime;

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
     * Get the last counter value recorded for the visitor.
     *
     * @return int|null
     */
    public function getLastCounter()
    {
        return !empty($this->visitor->last_counter) ? DateTime::format($this->visitor->last_counter, [
            'include_time'  => true,
            'exclude_year'  => true,
            'separator'     => ', '
        ]) : null;
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
     * Get user ID
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->visitor->user_id;
    }

    /**
     * Retrieves the first view time of the visitor.
     *
     * @return int|null The time of the first view, or null if not available.
     */
    public function getFirstView()
    {
        return !empty($this->visitor->first_view) ? DateTime::format($this->visitor->first_view, [
            'include_time'  => true,
            'exclude_year'  => true,
            'separator'     => ', '
        ]) : null;
    }

    /**
     * Retrieves the first page viewed by the visitor.
     *
     * @return string|null The first page viewed by the visitor, or null if not available.
     */
    public function getFirstPage()
    {
        return ! empty($this->visitor->first_page) ? Visitor::get_page_by_id($this->visitor->first_page) : null;
    }

    /**
     * Retrieves the last view time of the visitor.
     *
     * @param bool $raw Whether return raw value or formatted.
     * @return string The time of the last view, or null if not available.
     */
    public function getLastView($raw = false)
    {
        // Get date from last_view (DateTime), if not set use last_counter (Date)
        $date = $this->visitor->last_view ?? $this->visitor->last_counter;

        if ($raw) {
            return $date;
        }

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
     * Retrieves the date a certain page has been viewed
     *
     * @return string|null The last page viewed by the visitor, or null if not available.
     */
    public function getPageView()
    {
        return !empty($this->visitor->page_view) ? DateTime::format($this->visitor->page_view, [
            'include_time'  => true,
            'exclude_year'  => true,
            'separator'     => ', '
        ]) : null;
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
