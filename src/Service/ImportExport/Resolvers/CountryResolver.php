<?php

namespace WP_Statistics\Service\ImportExport\Resolvers;

use WP_Statistics\Abstracts\BaseRecord;
use WP_Statistics\Records\CountryRecord;

/**
 * Resolver for countries lookup table.
 *
 * Resolves country code (e.g., 'US') to country_id.
 *
 * @since 15.0.0
 */
class CountryResolver extends AbstractResolver
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $tableName = 'countries';

    /**
     * Get the unique key for caching from data.
     *
     * @param array $data Input data (expects 'code')
     * @return string Cache key
     */
    protected function getCacheKey(array $data): string
    {
        $code = $data['code'] ?? '';
        return strtoupper(trim($code));
    }

    /**
     * Get the lookup criteria for finding existing record.
     *
     * @param array $data Input data
     * @return array Lookup criteria
     */
    protected function getLookupCriteria(array $data): array
    {
        $code = $data['code'] ?? '';

        if (empty($code)) {
            return [];
        }

        return ['code' => strtoupper(trim($code))];
    }

    /**
     * Get the data to insert for a new record.
     *
     * @param array $data Input data
     * @return array Insert data
     */
    protected function getInsertData(array $data): array
    {
        $code = $data['code'] ?? '';

        if (empty($code)) {
            return [];
        }

        return [
            'code'           => strtoupper(trim($code)),
            'name'           => $data['name'] ?? '',
            'continent_code' => $data['continent_code'] ?? '',
            'continent'      => $data['continent'] ?? '',
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
        return $record->code ?? '';
    }

    /**
     * Create the record instance.
     *
     * @return BaseRecord
     */
    protected function createRecord(): BaseRecord
    {
        return new CountryRecord();
    }
}
