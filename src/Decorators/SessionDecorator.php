<?php

namespace WP_Statistics\Decorators;

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
     * @param mixed
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
        return empty($this->session->total_views) ? 0 : (int)$this->session->total_views;
    }

    /**
     * Get the visitor associated with this session.
     *
     * @return VisitorDecorator|null
     */
    public function getVisitor()
    {
        if (empty($this->session->visitor_id)) {
            return null;
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
            return null;
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
            return null;
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
            return null;
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
            return null;
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
            return null;
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
            return null;
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
            return null;
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
            return null;
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
            return null;
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
            return null;
        }
        $record = RecordFactory::timezone()->get(['ID' => $this->session->timezone_id]);
        return new TimezoneDecorator($record);
    }
}
