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

    public function __construct()
    {
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
