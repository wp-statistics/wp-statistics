<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\CountryRecord;
use WP_Statistics\Records\CityRecord;

/**
 * Entity for detecting and recording visitor's geographic information.
 *
 * This includes country and city lookups based on geolocation services.
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
        // Get raw location data
        $geo  = (array)$this->profile->getLocation();
        $code = isset($geo['country_code']) ? $geo['country_code'] : '';
        if (empty($code)) {
            return $this;
        }

        $cacheKey  = 'country_' . $code;
        $countryId = $this->getCachedData($cacheKey, function () use ($geo) {
            $model  = new CountryRecord();
            $record = $model->get(['code' => $geo['country_code']]);

            if (!empty($record) && isset($record->ID)) {
                return (int)$record->ID;
            }

            // Use profile getters for continent
            $continent = $this->profile->getContinent();

            return (int)$model->insert([
                'code'           => $geo['country_code'],
                'name'           => isset($geo['country_name']) ? $geo['country_name'] : '',
                'continent_code' => isset($geo['continent_code']) ? $geo['continent_code'] : '',
                'continent'      => $continent ?: (isset($geo['continent']) ? $geo['continent'] : ''),
            ]);
        });

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
        $geo = (array)$this->profile->getLocation();

        $countryId  = $this->profile->getCountryId();
        $cityName   = $this->profile->getCity();
        $regionName = $this->profile->getRegion();
        $regionCode = isset($geo['region_code']) ? $geo['region_code'] : '';

        if (empty($cityName) || $countryId < 1) {
            return $this;
        }

        $cacheKey = 'city_' . $countryId . '_' . md5($cityName);
        $cityId   = $this->getCachedData($cacheKey, function () use ($countryId, $regionCode, $regionName, $cityName) {
            $model  = new CityRecord();
            $record = $model->get([
                'country_id'  => $countryId,
                'region_code' => $regionCode,
                'city_name'   => $cityName,
            ]);

            if (!empty($record) && isset($record->ID)) {
                return (int)$record->ID;
            }

            return (int)$model->insert([
                'country_id'  => $countryId,
                'region_code' => $regionCode,
                'region_name' => $regionName,
                'city_name'   => $cityName,
            ]);
        });

        $this->profile->setCityId($cityId);
        return $this;
    }
}
