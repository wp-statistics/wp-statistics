<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `sessions` table.
 *
 * Provides methods to retrieve sessions by indexed fields such as visitor, country, device, and more.
 */
class SessionRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'sessions';

    /**
     * Get all sessions by visitor ID.
     *
     * @param int $visitorId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByVisitorId($visitorId)
    {
        return empty($visitorId) ? [] : $this->getAll(['visitor_id' => $visitorId]);
    }

    /**
     * Get all sessions by country ID.
     *
     * @param int $countryId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByCountryId($countryId)
    {
        return empty($countryId) ? [] : $this->getAll(['country_id' => $countryId]);
    }

    /**
     * Get all sessions by referrer ID.
     *
     * @param int $referrerId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByReferrerId($referrerId)
    {
        return empty($referrerId) ? [] : $this->getAll(['referrer_id' => $referrerId]);
    }

    /**
     * Get all sessions by city ID.
     *
     * @param int $cityId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByCityId($cityId)
    {
        return empty($cityId) ? [] : $this->getAll(['city_id' => $cityId]);
    }

    /**
     * Get all sessions by initial view ID.
     *
     * @param int $initialViewId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByInitialViewId($initialViewId)
    {
        return empty($initialViewId) ? [] : $this->getAll(['initial_view_id' => $initialViewId]);
    }

    /**
     * Get all sessions by last view ID.
     *
     * @param int $lastViewId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByLastViewId($lastViewId)
    {
        return empty($lastViewId) ? [] : $this->getAll(['last_view_id' => $lastViewId]);
    }

    /**
     * Get all sessions by device type ID.
     *
     * @param int $deviceTypeId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByDeviceTypeId($deviceTypeId)
    {
        return empty($deviceTypeId) ? [] : $this->getAll(['device_type_id' => $deviceTypeId]);
    }

    /**
     * Get all sessions by device OS ID.
     *
     * @param int $deviceOsId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByDeviceOsId($deviceOsId)
    {
        return empty($deviceOsId) ? [] : $this->getAll(['device_os_id' => $deviceOsId]);
    }

    /**
     * Get all sessions by device browser ID.
     *
     * @param int $browserId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByDeviceBrowserId($browserId)
    {
        return empty($browserId) ? [] : $this->getAll(['device_browser_id' => $browserId]);
    }

    /**
     * Get all sessions by browser version ID.
     *
     * @param int $versionId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByDeviceBrowserVersionId($versionId)
    {
        return empty($versionId) ? [] : $this->getAll(['device_browser_version_id' => $versionId]);
    }

    /**
     * Get all sessions by timezone ID.
     *
     * @param int $timezoneId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByTimezoneId($timezoneId)
    {
        return empty($timezoneId) ? [] : $this->getAll(['timezone_id' => $timezoneId]);
    }

    /**
     * Get all sessions by language ID.
     *
     * @param int $languageId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByLanguageId($languageId)
    {
        return empty($languageId) ? [] : $this->getAll(['language_id' => $languageId]);
    }

    /**
     * Get all sessions by resolution ID.
     *
     * @param int $resolutionId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByResolutionId($resolutionId)
    {
        return empty($resolutionId) ? [] : $this->getAll(['resolution_id' => $resolutionId]);
    }
}
