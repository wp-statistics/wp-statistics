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
     * Record all geographic information and return their IDs.
     *
     * @return array{country_id: int, city_id: int}
     */
    public function record(): array
    {
        $countryId = $this->isActive('countries') ? $this->recordCountry() : 0;

        return [
            'country_id' => $countryId,
            'city_id'    => $this->isActive('cities') ? $this->recordCity($countryId) : 0,
        ];
    }

    /**
     * Detect and record visitor's country based on geolocation data.
     *
     * @return int The country ID, or 0 if country code is empty.
     */
    private function recordCountry(): int
    {
        $geo = (array)$this->visitor->getLocation();

        $code = isset($geo['country_code']) ? $geo['country_code'] : '';

        if (empty($code)) {
            return 0;
        }

        $continent = $this->visitor->getContinent();

        return (int) RecordFactory::country()->upsert([
            'code'           => $geo['country_code'],
            'name'           => isset($geo['country']) ? $geo['country'] : '',
            'continent_code' => isset($geo['continent_code']) ? $geo['continent_code'] : '',
            'continent'      => $continent ?: (isset($geo['continent']) ? $geo['continent'] : ''),
        ]);
    }

    /**
     * Detect and record visitor's city based on geolocation data.
     *
     * @param int $countryId The country ID to associate the city with.
     * @return int The city ID, or 0 if city name is empty or country ID is missing.
     */
    private function recordCity(int $countryId): int
    {
        $geo = (array)$this->visitor->getLocation();

        $cityName   = $this->visitor->getCity();
        $regionName = $this->visitor->getRegion();
        $regionCode = isset($geo['region_code']) ? $geo['region_code'] : '';

        if (empty($cityName) || $countryId < 1) {
            return 0;
        }

        $record = RecordFactory::city()->get([
            'country_id'  => $countryId,
            'region_code' => $regionCode,
            'city_name'   => $cityName,
        ]);

        if (!empty($record) && isset($record->ID)) {
            return (int)$record->ID;
        }

        return (int)RecordFactory::city()->insert([
            'country_id'  => $countryId,
            'region_code' => $regionCode,
            'region_name' => $regionName ?? '',
            'city_name'   => $cityName,
        ]);
    }
}
