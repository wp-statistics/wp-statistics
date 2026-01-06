<?php

namespace WP_Statistics\Service\ImportExport\Resolvers;

use WP_Statistics\Abstracts\BaseRecord;
use WP_Statistics\Records\ResourceUriRecord;

/**
 * Resolver for resource_uris lookup table.
 *
 * Resolves resource_id + uri to resource_uri_id.
 *
 * @since 15.0.0
 */
class ResourceUriResolver extends AbstractResolver
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $tableName = 'resource_uris';

    /**
     * Get the unique key for caching from data.
     *
     * @param array $data Input data (expects 'resource_id', 'uri')
     * @return string Cache key
     */
    protected function getCacheKey(array $data): string
    {
        $resourceId = $data['resource_id'] ?? 0;
        $uri        = $data['uri'] ?? '';

        if (empty($resourceId) || empty($uri)) {
            return '';
        }

        return $resourceId . ':' . strtolower(trim($uri));
    }

    /**
     * Get the lookup criteria for finding existing record.
     *
     * @param array $data Input data
     * @return array Lookup criteria
     */
    protected function getLookupCriteria(array $data): array
    {
        $resourceId = $data['resource_id'] ?? 0;
        $uri        = $data['uri'] ?? '';

        if (empty($resourceId) || empty($uri)) {
            return [];
        }

        return [
            'resource_id' => (int)$resourceId,
            'uri'         => trim($uri),
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
        $resourceId = $data['resource_id'] ?? 0;
        $uri        = $data['uri'] ?? '';

        if (empty($resourceId) || empty($uri)) {
            return [];
        }

        return [
            'resource_id' => (int)$resourceId,
            'uri'         => trim($uri),
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
        $resourceId = $record->resource_id ?? 0;
        $uri        = $record->uri ?? '';

        if (empty($resourceId) || empty($uri)) {
            return '';
        }

        return $resourceId . ':' . strtolower($uri);
    }

    /**
     * Create the record instance.
     *
     * @return BaseRecord
     */
    protected function createRecord(): BaseRecord
    {
        return new ResourceUriRecord();
    }

    /**
     * Resolve by URI only (when resource_id not yet known).
     *
     * Looks up resource_uri by URI and returns both the resource_uri_id
     * and the associated resource_id.
     *
     * @param string $uri The URI to look up
     * @return array|null ['id' => resource_uri_id, 'resource_id' => resource_id] or null
     */
    public function resolveByUri(string $uri): ?array
    {
        $uri = trim($uri);

        if (empty($uri)) {
            return null;
        }

        $record = $this->getRecord();
        $row    = $record->get(['uri' => $uri]);

        if ($row && !empty($row->ID)) {
            return [
                'id'          => (int)$row->ID,
                'resource_id' => (int)$row->resource_id,
            ];
        }

        return null;
    }
}
