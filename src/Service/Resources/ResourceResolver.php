<?php

namespace WP_Statistics\Service\Resources;

use WP_Statistics\Records\RecordFactory;

/**
 * Resolves resource and resource_uri records from raw parameters.
 *
 * Unlike ResourceManager which relies on WordPress page context (query loop,
 * conditional tags), this class works with explicit parameters — making it
 * suitable for headless clients and any context where the resource data
 * is provided directly rather than detected from the current request.
 */
class ResourceResolver
{
    /**
     * Find or create a resource_uri record from raw parameters.
     *
     * Looks up the resource by (resource_id, resource_type), creating it if needed,
     * then looks up the URI record by (resource.ID, uri), creating it if needed.
     *
     * @param int|null $resourceId   The logical resource ID (e.g. post ID).
     * @param string   $resourceType The resource type (e.g. 'post', 'page').
     * @param string   $uri          The URI path (e.g. '/hello-world').
     * @return int The resource_uri record ID, or 0 on failure.
     */
    public static function resolveUriId(?int $resourceId, string $resourceType, string $uri): int
    {
        // Find or create resource record
        $resource = RecordFactory::resource()->get([
            'resource_id'   => $resourceId,
            'resource_type' => $resourceType,
        ]);

        $resourceRowId = !empty($resource)
            ? (int) $resource->ID
            : (int) RecordFactory::resource()->insert([
                'resource_id'   => $resourceId,
                'resource_type' => $resourceType,
            ]);

        if ($resourceRowId < 1) {
            return 0;
        }

        // Find or create URI record
        $uriRecord = RecordFactory::resourceUri()->get([
            'resource_id' => $resourceRowId,
            'uri'         => $uri,
        ]);

        if (!empty($uriRecord)) {
            return (int) $uriRecord->ID;
        }

        return (int) RecordFactory::resourceUri()->insert([
            'resource_id' => $resourceRowId,
            'uri'         => $uri,
        ]);
    }
}
