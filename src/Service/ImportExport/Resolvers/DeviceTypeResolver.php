<?php

namespace WP_Statistics\Service\ImportExport\Resolvers;

use WP_Statistics\Abstracts\BaseRecord;
use WP_Statistics\Records\DeviceTypeRecord;

/**
 * Resolver for device_types lookup table.
 *
 * Resolves device type name (e.g., 'desktop', 'mobile') to device_type_id.
 *
 * @since 15.0.0
 */
class DeviceTypeResolver extends AbstractResolver
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $tableName = 'device_types';

    /**
     * Get the unique key for caching from data.
     *
     * @param array $data Input data (expects 'name')
     * @return string Cache key
     */
    protected function getCacheKey(array $data): string
    {
        $name = $data['name'] ?? '';
        return strtolower(trim($name));
    }

    /**
     * Get the lookup criteria for finding existing record.
     *
     * @param array $data Input data
     * @return array Lookup criteria
     */
    protected function getLookupCriteria(array $data): array
    {
        $name = $data['name'] ?? '';

        if (empty($name)) {
            return [];
        }

        return ['name' => trim($name)];
    }

    /**
     * Get the data to insert for a new record.
     *
     * @param array $data Input data
     * @return array Insert data
     */
    protected function getInsertData(array $data): array
    {
        $name = $data['name'] ?? '';

        if (empty($name)) {
            return [];
        }

        return ['name' => trim($name)];
    }

    /**
     * Get cache key from a database record.
     *
     * @param object $record Database record
     * @return string Cache key
     */
    protected function getCacheKeyFromRecord(object $record): string
    {
        return strtolower($record->name ?? '');
    }

    /**
     * Create the record instance.
     *
     * @return BaseRecord
     */
    protected function createRecord(): BaseRecord
    {
        return new DeviceTypeRecord();
    }
}
