<?php

namespace WP_Statistics\Decorators;

use WP_STATISTICS\IP;
use WP_Statistics\Records\RecordFactory;

/**
 * Decorator for a record from the 'sessions' table.
 *
 * Provides accessors for session properties and related entities via
 * their respective decorators.
 */
class SessionDecorator
{
    /**
     * The session record.
     *
     * @var mixed
     */
    private $session;

    /**
     * SessionDecorator constructor.
     *
     * @param object|null $session A stdClass for a 'sessions' row, or null.
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * Get session ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return empty($this->session->ID) ? null : (int)$this->session->ID;
    }

    /**
     * Get IP address.
     *
     * @return string|null
     */
    public function getIp()
    {
        return $this->session->ip ?? null;
    }

    /**
     * Get the initial view associated with the session.
     *
     * @return ViewDecorator|null
     */
    public function getInitialView()
    {
        if (empty($this->session->initial_view_id)) {
            return new ViewDecorator(null);
        }

        $record = RecordFactory::view()->get(['ID' => $this->session->initial_view_id]);

        return new ViewDecorator($record);
    }

    /**
     * Get the last view associated with the session.
     *
     * @return ViewDecorator|null
     */
    public function getLastView()
    {
        if (empty($this->session->last_view_id)) {
            return new ViewDecorator(null);
        }

        $record = RecordFactory::view()->get(['ID' => $this->session->last_view_id]);

        return new ViewDecorator($record);
    }

    /**
     * Get session start timestamp.
     *
     * @return string|null
     */
    public function getStartedAt()
    {
        return $this->session->started_at ?? null;
    }

    /**
     * Get session end timestamp.
     *
     * @return string|null
     */
    public function getEndedAt()
    {
        return $this->session->ended_at ?? null;
    }

    /**
     * Get session duration in seconds.
     *
     * @return int|null
     */
    public function getDuration()
    {
        return empty($this->session->duration) ? null : (int)$this->session->duration;
    }

    /**
     * Get total views in this session.
     *
     * @return int
     */
    public function getViews()
    {
        return empty($this->session->total_views) ? 0 : number_format_i18n($this->session->total_views);
    }

    /**
     * Get the visitor associated with this session.
     *
     * @return VisitorDecorator|null
     */
    public function getVisitor()
    {
        if (empty($this->session->visitor_id)) {
            return new VisitorDecorator(null);
        }

        $record = RecordFactory::visitor()->get(['ID' => $this->session->visitor_id]);
        return new VisitorDecorator($record);
    }

    /**
     * Get the country associated with this session.
     *
     * @return CountryDecorator|null
     */
    public function getCountry()
    {
        if (empty($this->session->country_id)) {
            return new CountryDecorator(null);
        }

        $record = RecordFactory::country()->get(['ID' => $this->session->country_id]);
        return new CountryDecorator($record);
    }

    /**
     * Get the city associated with this session.
     *
     * @return CityDecorator|null
     */
    public function getCity()
    {
        if (empty($this->session->city_id)) {
            return new CityDecorator(null);
        }

        $record = RecordFactory::city()->get(['ID' => $this->session->city_id]);
        return new CityDecorator($record);
    }

    /**
     * Get the referral associated with this session.
     *
     * @return ReferrerDecorator|null
     */
    public function getReferral()
    {
        if (empty($this->session->referrer_id)) {
            return new ReferrerDecorator(null);
        }

        $record = RecordFactory::referrer()->get(['ID' => $this->session->referrer_id]);
        return new ReferrerDecorator($record);
    }

    /**
     * Get the device type for this session.
     *
     * @return DeviceTypeDecorator|null
     */
    public function getDeviceType()
    {
        if (empty($this->session->device_type_id)) {
            return new DeviceTypeDecorator(null);
        }

        $record = RecordFactory::deviceType()->get(['ID' => $this->session->device_type_id]);
        return new DeviceTypeDecorator($record);
    }

    /**
     * Get the operating system for this session.
     *
     * @return DeviceOsDecorator|null
     */
    public function getOs()
    {
        if (empty($this->session->device_os_id)) {
            return new DeviceOsDecorator(null);
        }

        $record = RecordFactory::deviceOs()->get(['ID' => $this->session->device_os_id]);
        return new DeviceOsDecorator($record);
    }

    /**
     * Get the browser for this session.
     *
     * @return DeviceBrowserDecorator|null
     */
    public function getBrowser()
    {
        if (empty($this->session->device_browser_id)) {
            return new DeviceBrowserDecorator(null);
        }

        $record = RecordFactory::deviceBrowser()->get(['ID' => $this->session->device_browser_id]);
        return new DeviceBrowserDecorator($record);
    }

    /**
     * Get the browser version for this session.
     *
     * @return DeviceBrowserVersionDecorator|null
     */
    public function getBrowserVersion()
    {
        if (empty($this->session->device_browser_version_id)) {
            return new DeviceBrowserVersionDecorator(null);
        }

        $record = RecordFactory::deviceBrowserVersion()->get(['ID' => $this->session->device_browser_version_id]);
        return new DeviceBrowserVersionDecorator($record);
    }

    /**
     * Get the screen resolution for this session.
     *
     * @return ResolutionDecorator|null
     */
    public function getResolution()
    {
        if (empty($this->session->resolution_id)) {
            return new ResolutionDecorator(null);
        }

        $record = RecordFactory::resolution()->get(['ID' => $this->session->resolution_id]);
        return new ResolutionDecorator($record);
    }

    /**
     * Get the language for this session.
     *
     * @return LanguageDecorator|null
     */
    public function getLanguage()
    {
        if (empty($this->session->language_id)) {
            return new LanguageDecorator(null);
        }

        $record = RecordFactory::language()->get(['ID' => $this->session->language_id]);
        return new LanguageDecorator($record);
    }

    /**
     * Get the timezone for this session.
     *
     * @return TimezoneDecorator|null
     */
    public function getTimezone()
    {
        if (empty($this->session->timezone_id)) {
            return new TimezoneDecorator(null);
        }

        $record = RecordFactory::timezone()->get(['ID' => $this->session->timezone_id]);
        return new TimezoneDecorator($record);
    }

    /**
     * Checks whether the visitor is a logged-in user.
     *
     * @return bool
     */
    public function isLoggedInUser()
    {
        return !empty($this->session->user_id);
    }

    /**
     * Get the visitor's user object (if logged in).
     *
     * @return UserDecorator|null
     */
    public function getUser()
    {
        if ($this->getUserId()) {
            return new UserDecorator($this->getUserId());
        }

        return null;
    }

    /**
     * Get user ID
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->session->user_id;
    }

    /**
     * Get a parameter decorator scoped to the current session and a resource.
     *
     * @param int $resourceId Resource ID to filter by.
     * @return ParameterDecorator
     */
    public function getParameter($resourceId)
    {
        if (empty($this->session->ID) || empty($resourceId)) {
            return new ParameterDecorator(null);
        }

        $record = RecordFactory::parameter()->get([
            'session_id'  => $this->session->ID,
            'resource_id' => $resourceId,
        ]);

        return new ParameterDecorator($record);
    }

    /**
     * Is the visitor's IP hashed?
     *
     * @return string|null
     */
    public function isHashedIP()
    {
        return IP::IsHashIP($this->getIp());
    }
}
