<?php

namespace WP_Statistics\Service\ImportExport\Resolvers;

use WP_Statistics\Abstracts\BaseRecord;
use WP_Statistics\Records\DeviceBrowserVersionRecord;

/**
 * Resolver for device_browser_versions lookup table.
 *
 * Resolves browser_id + version to device_browser_version_id.
 *
 * @since 15.0.0
 */
class DeviceBrowserVersionResolver extends AbstractResolver
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $tableName = 'device_browser_versions';

    /**
     * Get the unique key for caching from data.
     *
     * @param array $data Input data (expects 'browser_id', 'version')
     * @return string Cache key
     */
    protected function getCacheKey(array $data): string
    {
        $browserId = $data['browser_id'] ?? 0;
        $version   = $data['version'] ?? '';

        if (empty($browserId) || empty($version)) {
            return '';
        }

        return $browserId . ':' . strtolower(trim($version));
    }

    /**
     * Get the lookup criteria for finding existing record.
     *
     * @param array $data Input data
     * @return array Lookup criteria
     */
    protected function getLookupCriteria(array $data): array
    {
        $browserId = $data['browser_id'] ?? 0;
        $version   = $data['version'] ?? '';

        if (empty($browserId) || empty($version)) {
            return [];
        }

        return [
            'browser_id' => (int)$browserId,
            'version'    => trim($version),
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
        $browserId = $data['browser_id'] ?? 0;
        $version   = $data['version'] ?? '';

        if (empty($browserId) || empty($version)) {
            return [];
        }

        return [
            'browser_id' => (int)$browserId,
            'version'    => trim($version),
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
        $browserId = $record->browser_id ?? 0;
        $version   = $record->version ?? '';

        if (empty($browserId) || empty($version)) {
            return '';
        }

        return $browserId . ':' . strtolower($version);
    }

    /**
     * Create the record instance.
     *
     * @return BaseRecord
     */
    protected function createRecord(): BaseRecord
    {
        return new DeviceBrowserVersionRecord();
    }
}
