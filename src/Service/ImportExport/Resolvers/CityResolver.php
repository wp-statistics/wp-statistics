<?php

namespace WP_Statistics\Service\ImportExport\Resolvers;

use WP_Statistics\Abstracts\BaseRecord;
use WP_Statistics\Records\CityRecord;

/**
 * Resolver for cities lookup table.
 *
 * Resolves city name + country_id to city_id.
 *
 * @since 15.0.0
 */
class CityResolver extends AbstractResolver
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $tableName = 'cities';

    /**
     * Get the unique key for caching from data.
     *
     * @param array $data Input data (expects 'city_name', 'country_id')
     * @return string Cache key
     */
    protected function getCacheKey(array $data): string
    {
        $cityName  = $data['city_name'] ?? '';
        $countryId = $data['country_id'] ?? 0;

        if (empty($cityName) || empty($countryId)) {
            return '';
        }

        return strtolower(trim($cityName)) . ':' . $countryId;
    }

    /**
     * Get the lookup criteria for finding existing record.
     *
     * @param array $data Input data
     * @return array Lookup criteria
     */
    protected function getLookupCriteria(array $data): array
    {
        $cityName  = $data['city_name'] ?? '';
        $countryId = $data['country_id'] ?? 0;

        if (empty($cityName) || empty($countryId)) {
            return [];
        }

        return [
            'city_name'  => trim($cityName),
            'country_id' => (int)$countryId,
        ];
    }

    /**
     * Get the data to insert for a new record.
     *
     * @param array $data Input data
     * @return array Insert data
     */
    protected function getInsertData(array $data): array
    {
        $cityName  = $data['city_name'] ?? '';
        $countryId = $data['country_id'] ?? 0;

        if (empty($cityName) || empty($countryId)) {
            return [];
        }

        return [
            'city_name'   => trim($cityName),
            'country_id'  => (int)$countryId,
            'region_code' => $data['region_code'] ?? '',
            'region_name' => $data['region_name'] ?? '',
        ];
    }

    /**
     * Get cache key from a database record.
     *
     * @param object $record Database record
     * @return string Cache key
     */
    protected function getCacheKeyFromRecord(object $record): string
    {
        $cityName  = $record->city_name ?? '';
        $countryId = $record->country_id ?? 0;

        if (empty($cityName) || empty($countryId)) {
            return '';
        }

        return strtolower($cityName) . ':' . $countryId;
    }

    /**
     * Create the record instance.
     *
     * @return BaseRecord
     */
    protected function createRecord(): BaseRecord
    {
        return new CityRecord();
    }
}
