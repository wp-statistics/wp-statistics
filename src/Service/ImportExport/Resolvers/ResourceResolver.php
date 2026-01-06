<?php

namespace WP_Statistics\Service\ImportExport\Resolvers;

use WP_Statistics\Abstracts\BaseRecord;
use WP_Statistics\Records\ResourceRecord;

/**
 * Resolver for resources lookup table.
 *
 * Resolves resource_type + resource_id to resources.ID.
 *
 * @since 15.0.0
 */
class ResourceResolver extends AbstractResolver
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $tableName = 'resources';

    /**
     * Get the unique key for caching from data.
     *
     * @param array $data Input data (expects 'resource_type', 'resource_id')
     * @return string Cache key
     */
    protected function getCacheKey(array $data): string
    {
        $resourceType = $data['resource_type'] ?? '';
        $resourceId   = $data['resource_id'] ?? 0;

        if (empty($resourceType) || empty($resourceId)) {
            return '';
        }

        return strtolower(trim($resourceType)) . ':' . $resourceId;
    }

    /**
     * Get the lookup criteria for finding existing record.
     *
     * @param array $data Input data
     * @return array Lookup criteria
     */
    protected function getLookupCriteria(array $data): array
    {
        $resourceType = $data['resource_type'] ?? '';
        $resourceId   = $data['resource_id'] ?? 0;

        if (empty($resourceType) || empty($resourceId)) {
            return [];
        }

        return [
            'resource_type' => trim($resourceType),
            'resource_id'   => (int)$resourceId,
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
        $resourceType = $data['resource_type'] ?? '';
        $resourceId   = $data['resource_id'] ?? 0;

        if (empty($resourceType) || empty($resourceId)) {
            return [];
        }

        return [
            'resource_type'    => trim($resourceType),
            'resource_id'      => (int)$resourceId,
            'cached_title'     => $data['cached_title'] ?? null,
            'cached_terms'     => $data['cached_terms'] ?? null,
            'cached_author_id' => $data['cached_author_id'] ?? null,
            'cached_date'      => $data['cached_date'] ?? null,
            'resource_meta'    => $data['resource_meta'] ?? null,
            'language'         => $data['language'] ?? null,
            'is_deleted'       => $data['is_deleted'] ?? 0,
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
        $resourceType = $record->resource_type ?? '';
        $resourceId   = $record->resource_id ?? 0;

        if (empty($resourceType) || empty($resourceId)) {
            return '';
        }

        return strtolower($resourceType) . ':' . $resourceId;
    }

    /**
     * Create the record instance.
     *
     * @return BaseRecord
     */
    protected function createRecord(): BaseRecord
    {
        return new ResourceRecord();
    }
}
