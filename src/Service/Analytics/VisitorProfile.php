<?php

namespace WP_Statistics\Service\Analytics;

use WP_STATISTICS\IP;
use WP_Statistics\Traits\ObjectCacheTrait;
use WP_STATISTICS\User;
use WP_STATISTICS\Pages;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_STATISTICS\Visitor;
use WP_Statistics\Service\Analytics\DeviceDetection\UserAgent;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Analytics\Referrals\Referrals;
use WP_Statistics\Service\Analytics\Referrals\SourceDetector;

/**
 * @todo Replace object cache internally with ObjectCacheTrait
 */
class VisitorProfile
{
    use ObjectCacheTrait;

    /**
     * Visitor record ID.
     *
     * @var string
     */
    private const META_VISITOR_ID = 'visitor_id';

    /**
     * Session record ID.
     *
     * @var string
     */
    private const META_SESSION_ID = 'session_id';

    /**
     * View record ID.
     *
     * @var string
     */
    private const META_VIEW_ID = 'view_id';

    /**
     * Resource record ID.
     *
     * @var string
     */
    private const META_RESOURCE_ID = 'resource_id';

    /**
     * Referrer record ID.
     *
     * @var string
     */
    private const META_REFERRER_ID = 'referrer_id';

    /**
     * Device type record ID.
     *
     * @var string
     */
    private const META_DEVICE_TYPE_ID = 'device_type_id';

    /**
     * Device operating system record ID.
     *
     * @var string
     */
    private const META_DEVICE_OS_ID = 'device_os_id';

    /**
     * Device browser record ID.
     *
     * @var string
     */
    private const META_DEVICE_BROWSER_ID = 'device_browser_id';

    /**
     * Device browser version record ID.
     *
     * @var string
     */
    private const META_DEVICE_BROWSER_VERSION_ID = 'device_browser_version_id';

    /**
     * Screen resolution record ID.
     *
     * @var string
     */
    private const META_RESOLUTION_ID = 'resolution_id';

    /**
     * Country record ID.
     *
     * @var string
     */
    private const META_COUNTRY_ID = 'country_id';

    /**
     * City record ID.
     *
     * @var string
     */
    private const META_CITY_ID = 'city_id';

    /**
     * Language record ID.
     *
     * @var string
     */
    private const META_LANGUAGE_ID = 'language_id';

    /**
     * Timezone record ID.
     *
     * @var string
     */
    private const META_TIMEZONE_ID = 'timezone_id';

    /**
     * Duration between views, in milliseconds.
     *
     * @var string
     */
    private const META_DURATION = 'duration';

    /**
     * Holds visitor tracking metadata loaded from the database.
     *
     * Includes visitor_id, session_id, view_id, resource_id, user_id and other tracking-related fields.
     * These values are retrieved from or synchronized with the database during the session lifecycle.
     *
     * @var array
     */
    protected $meta = [];

    public function __construct()
    {
    }

    /**
     * Store the Visitor record ID into internal metadata.
     *
     * @param int $id Visitor record ID.
     * @return void
     */
    public function setVisitorId($id)
    {
        $this->setMeta(self::META_VISITOR_ID, $id);
    }

    /**
     * Retrieve the Visitor record ID from internal metadata.
     *
     * @return int Visitor ID, or 0 if not set.
     */
    public function getVisitorIdMeta()
    {
        return (int)$this->getMeta(self::META_VISITOR_ID, 0);
    }

    /**
     * Store the Session record ID into internal metadata.
     *
     * @param int $id Session record ID.
     * @return void
     */
    public function setSessionId($id)
    {
        $this->setMeta(self::META_SESSION_ID, $id);
    }

    /**
     * Retrieve the Session record ID from internal metadata.
     *
     * @return int Session ID, or 0 if not set.
     */
    public function getSessionId()
    {
        return (int)$this->getMeta(self::META_SESSION_ID, 0);
    }

    /**
     * Store the Resource record ID into internal metadata.
     *
     * @param int $id Resource record ID.
     * @return void
     */
    public function setResourceId($id)
    {
        $this->setMeta(self::META_RESOURCE_ID, $id);
    }

    /**
     * Retrieve the Resource record ID from internal metadata.
     *
     * @return int Resource ID, or 0 if not set.
     */
    public function getResourceId()
    {
        return (int)$this->getMeta(self::META_RESOURCE_ID, 0);
    }

    /**
     * Store the View record ID into internal metadata.
     *
     * @param int $id View record ID.
     * @return void
     */
    public function setViewId($id)
    {
        $this->setMeta(self::META_VIEW_ID, $id);
    }

    /**
     * Retrieve the View record ID from internal metadata.
     *
     * @return int View ID, or 0 if not set.
     */
    public function getViewId()
    {
        return (int)$this->getMeta(self::META_VIEW_ID, 0);
    }

    /**
     * Store the Device Type record ID into internal metadata.
     *
     * @param int $id Device Type ID.
     * @return void
     */
    public function setDeviceTypeId($id)
    {
        $this->setMeta(self::META_DEVICE_TYPE_ID, $id);
    }

    /**
     * Retrieve the Device Type record ID from internal metadata.
     *
     * @return int Device Type ID, or 0 if not set.
     */
    public function getDeviceTypeId()
    {
        return (int)$this->getMeta(self::META_DEVICE_TYPE_ID, 0);
    }

    /**
     * Store the Device Operating System record ID into internal metadata.
     *
     * @param int $id Device OS ID.
     * @return void
     */
    public function setDeviceOsId($id)
    {
        $this->setMeta(self::META_DEVICE_OS_ID, $id);
    }

    /**
     * Retrieve the Device Operating System record ID from internal metadata.
     *
     * @return int Device OS ID, or 0 if not set.
     */
    public function getDeviceOsId()
    {
        return (int)$this->getMeta(self::META_DEVICE_OS_ID, 0);
    }

    /**
     * Store the Device Browser record ID into internal metadata.
     *
     * @param int $id Device Browser ID.
     * @return void
     */
    public function setDeviceBrowserId($id)
    {
        $this->setMeta(self::META_DEVICE_BROWSER_ID, $id);
    }

    /**
     * Retrieve the Device Browser record ID from internal metadata.
     *
     * @return int Device Browser ID, or 0 if not set.
     */
    public function getDeviceBrowserId()
    {
        return (int)$this->getMeta(self::META_DEVICE_BROWSER_ID, 0);
    }

    /**
     * Store the Device Browser Version record ID into internal metadata.
     *
     * @param int $id Device Browser Version ID.
     * @return void
     */
    public function setDeviceBrowserVersionId($id)
    {
        $this->setMeta(self::META_DEVICE_BROWSER_VERSION_ID, $id);
    }

    /**
     * Retrieve the Device Browser Version record ID from internal metadata.
     *
     * @return int Device Browser Version ID, or 0 if not set.
     */
    public function getDeviceBrowserVersionId()
    {
        return (int)$this->getMeta(self::META_DEVICE_BROWSER_VERSION_ID, 0);
    }

    /**
     * Store the Screen Resolution record ID into internal metadata.
     *
     * @param int $id Resolution ID.
     * @return void
     */
    public function setResolutionId($id)
    {
        $this->setMeta(self::META_RESOLUTION_ID, $id);
    }

    /**
     * Retrieve the Screen Resolution record ID from internal metadata.
     *
     * @return int Resolution ID, or 0 if not set.
     */
    public function getResolutionId()
    {
        return (int)$this->getMeta(self::META_RESOLUTION_ID, 0);
    }

    /**
     * Store the Country record ID into internal metadata.
     *
     * @param int $id Country ID.
     * @return void
     */
    public function setCountryId($id)
    {
        $this->setMeta(self::META_COUNTRY_ID, $id);
    }

    /**
     * Retrieve the Country record ID from internal metadata.
     *
     * @return int Country ID, or 0 if not set.
     */
    public function getCountryId()
    {
        return (int)$this->getMeta(self::META_COUNTRY_ID, 0);
    }

    /**
     * Store the City record ID into internal metadata.
     *
     * @param int $id City ID.
     * @return void
     */
    public function setCityId($id)
    {
        $this->setMeta(self::META_CITY_ID, $id);
    }

    /**
     * Retrieve the City record ID from internal metadata.
     *
     * @return int City ID, or 0 if not set.
     */
    public function getCityId()
    {
        return (int)$this->getMeta(self::META_CITY_ID, 0);
    }

    /**
     * Store the Language record ID into internal metadata.
     *
     * @param int $id Language ID.
     * @return void
     */
    public function setLanguageId($id)
    {
        $this->setMeta(self::META_LANGUAGE_ID, $id);
    }

    /**
     * Retrieve the Language record ID from internal metadata.
     *
     * @return int Language ID, or 0 if not set.
     */
    public function getLanguageId()
    {
        return (int)$this->getMeta(self::META_LANGUAGE_ID, 0);
    }

    /**
     * Store the Timezone record ID into internal metadata.
     *
     * @param int $id Timezone ID.
     * @return void
     */
    public function setTimezoneId($id)
    {
        $this->setMeta(self::META_TIMEZONE_ID, $id);
    }

    /**
     * Retrieve the Timezone record ID from internal metadata.
     *
     * @return int Timezone ID, or 0 if not set.
     */
    public function getTimezoneId()
    {
        return (int)$this->getMeta(self::META_TIMEZONE_ID, 0);
    }

    /**
     * Store the Referrer record ID into internal metadata.
     *
     * @param int $id Referrer ID.
     * @return void
     */
    public function setReferrerId($id)
    {
        $this->setMeta(self::META_REFERRER_ID, $id);
    }

    /**
     * Retrieve the Referrer record ID from internal metadata.
     *
     * @return int Referrer ID, or 0 if not set.
     */
    public function getReferrerId()
    {
        return (int)$this->getMeta(self::META_REFERRER_ID, 0);
    }

    /**
     * Store the view duration (milliseconds) into internal metadata.
     *
     * @param int $duration View duration in milliseconds.
     * @return void
     */
    public function setDuration($duration)
    {
        $this->setMeta(self::META_DURATION, $duration);
    }

    /**
     * Retrieve the view duration (milliseconds) from internal metadata.
     *
     * @return int View duration, or 0 if not set.
     */
    public function getDuration()
    {
        return (int)$this->getMeta(self::META_DURATION, 0);
    }

    /**
     * Set a metadata key/value pair during runtime.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }

    /**
     * Retrieve a previously set metadata value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    public function getMeta($key, $default = null)
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : $default;
    }

    /**
     * Magic method to dynamically set properties if they exist.
     *
     * @param string $name The name of the property.
     * @param mixed $value The value to assign to the property.
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    /**
     * Get the visitor ID using caching to prevent redundant lookups.
     *
     * @return int The visitor ID.
     */
    public function getVisitorId()
    {
        return $this->getCachedData('visitorId', function () {
            $visitor = Visitor::exist_ip_in_day($this->getProcessedIPForStorage());
            return $visitor->ID ?? 0;
        });
    }

    /**
     * Get the visitor's IP address, cached to avoid multiple retrievals.
     *
     * @return string The IP address.
     */
    public function getIp()
    {
        return $this->getCachedData('ip', function () {
            return IP::getIP();
        });
    }

    /**
     * Get the processed IP address for storage, using caching.
     *
     * @return string The processed IP for storage.
     */
    public function getProcessedIPForStorage()
    {
        return $this->getCachedData('processedIPForStorage', function () {
            return IP::getStoreIP();
        });
    }

    /**
     * Check if the IP is active today, cached for performance.
     *
     * @return object visitor object if active today, false otherwise.
     */
    public function isIpActiveToday()
    {
        return $this->getCachedData('isIpActiveToday', function () {
            return Visitor::exist_ip_in_day($this->getProcessedIPForStorage());
        });
    }

    /**
     * Get the visitor's location, with optional specific location information.
     *
     * @param string|null $location Specific location data to retrieve.
     * @return array|string|null The location data or specific part if requested.
     */
    public function getLocation($location = null)
    {
        $locationData = $this->getCachedData('location', function () {
            return GeolocationFactory::getLocation($this->getIp());
        });

        if ($location) {
            return $locationData[$location];
        }

        return $locationData;
    }

    /**
     * Get the visitor's country from the location data.
     *
     * @return string The country.
     */
    public function getCountry()
    {
        return $this->getLocation('country_code');
    }

    /**
     * Get the visitor's city from the location data.
     *
     * @return string The city.
     */
    public function getCity()
    {
        return $this->getLocation('city');
    }

    /**
     * Get the visitor's region from the location data.
     *
     * @return string The region.
     */
    public function getRegion()
    {
        return $this->getLocation('region');
    }

    /**
     * Get the visitor's region code from the location data.
     *
     * @return string The region code.
     */
    public function getRegionCode()
    {
        return $this->getLocation('region_code');
    }

    /**
     * Get the visitor's continent from the location data.
     *
     * @return string The continent.
     */
    public function getContinent()
    {
        return $this->getLocation('continent');
    }

    /**
     * Get the visitor's latitude from the location data.
     *
     * @return float The latitude.
     */
    public function getLatitude()
    {
        return $this->getLocation('latitude');
    }

    /**
     * Get the visitor's longitude from the location data.
     *
     * @return float The longitude.
     */
    public function getLongitude()
    {
        return $this->getLocation('longitude');
    }

    /**
     * Check if the visitor is referred from another site, or not.
     *
     * @return bool
     */
    public function isReferred()
    {
        return !empty(Referrals::getUrl()) ? true : false;
    }

    /**
     * Get the visitor's referrer URL, cached for reuse.
     *
     * @return string The referrer URL.
     */
    public function getReferrer()
    {
        return $this->getCachedData('referrer', function () {
            return Referrals::getUrl();
        });
    }

    /**
     * Get the visitor's source info
     *
     * @return SourceDetector The source channel.
     */
    public function getSource()
    {
        return $this->getCachedData('source', function () {
            return Referrals::getSource();
        });
    }


    /**
     * Get the visitor's user agent information, cached for reuse.
     *
     * @return array|DeviceDetection\UserAgentService|null The user agent details.
     */
    public function getUserAgent()
    {
        return $this->getCachedData('userAgent', function () {
            return UserAgent::getUserAgent();
        });
    }

    /**
     * Get the HTTP user agent string, cached for reuse.
     *
     * @return string The HTTP user agent.
     */
    public function getHttpUserAgent()
    {
        return $this->getCachedData('httpUserAgent', function () {
            return UserAgent::getHttpUserAgent();
        });
    }

    /**
     * Get the current request URI, cached for reuse.
     *
     * @return string The request URI.
     */
    public function getRequestUri()
    {
        return $this->getCachedData('requestUri', function () {
            return Helper::getRequestUri();
        });
    }

    /**
     * Get the user ID of the visitor, with caching for better performance.
     *
     * @return int The user ID or 0 if anonymous tracking is enabled.
     */
    public function getUserId()
    {
        return $this->getCachedData('userId', function () {
            if (!Option::get('visitors_log') || Helper::shouldTrackAnonymously()) {
                return 0;
            } else {
                return User::get_user_id();
            }
        });
    }

    /**
     * Get the type of the current page the visitor is viewing, cached for reuse.
     *
     * @return string The page type.
     */
    public function getCurrentPageType()
    {
        return $this->getCachedData('currentPageType', function () {
            return Pages::get_page_type();
        });
    }
}
