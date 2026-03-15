<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\RecordFactory;

/**
 * Entity for detecting and recording visitor's geographic information.
 *
 * This includes country and city lookups based on geolocation services.
 *
 * @since 15.0.0
 */
class Geo extends BaseEntity
{
    /**
     * Detect and record visitor's country based on geolocation data.
     *
     * @return $this
     */
    public function recordCountry()
    {
        if (!$this->isActive('countries')) {
            return $this;
        }

        $geo = (array)$this->profile->getLocation();

        $code = isset($geo['country_code']) ? $geo['country_code'] : '';

        if (empty($code)) {
            return $this;
        }

        $record = RecordFactory::country()->get(['code' => $geo['country_code']]);

        if (!empty($record) && isset($record->ID)) {
            $this->profile->setCountryId((int)$record->ID);
            return $this;
        }

        $continent = $this->profile->getContinent();

        $countryId = (int)RecordFactory::country()->insert([
            'code'           => $geo['country_code'],
            'name'           => isset($geo['country']) ? $geo['country'] : '',
            'continent_code' => isset($geo['continent_code']) ? $geo['continent_code'] : '',
            'continent'      => $continent ?: (isset($geo['continent']) ? $geo['continent'] : ''),
        ]);

        $this->profile->setCountryId($countryId);
        return $this;
    }

    /**
     * Detect and record visitor's city based on geolocation data.
     *
     * @return $this
     */
    public function recordCity()
    {
        if (!$this->isActive('cities')) {
            return $this;
        }

        $geo = (array)$this->profile->getLocation();

        $countryId  = $this->profile->getCountryId();
        $cityName   = $this->profile->getCity();
        $regionName = $this->profile->getRegion();
        $regionCode = isset($geo['region_code']) ? $geo['region_code'] : '';

        if (empty($cityName) || $countryId < 1) {
            return $this;
        }

        $record = RecordFactory::city()->get([
            'country_id'  => $countryId,
            'region_code' => $regionCode,
            'city_name'   => $cityName,
        ]);

        if (!empty($record) && isset($record->ID)) {
            $this->profile->setCityId((int)$record->ID);
            return $this;
        }

        $cityId = (int)RecordFactory::city()->insert([
            'country_id'  => $countryId,
            'region_code' => $regionCode,
            'region_name' => $regionName ?? '',
            'city_name'   => $cityName,
        ]);

        $this->profile->setCityId($cityId);
        return $this;
    }
}
