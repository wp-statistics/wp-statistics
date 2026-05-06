<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `resource_uris` table.
 *
 * This class provides convenience methods for retrieving resource URL records
 * by common filters such as resource ID or URL.
 *
 * @since 15.0.0
 */
class ResourceUriRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'resource_uris';

    /**
     * Atomic find-or-create by (resource_id, uri).
     *
     * Returns the existing row's ID if a duplicate match is found, otherwise
     * inserts a new row and returns its ID. Race-safe — concurrent callers
     * cannot create duplicate URI rows.
     *
     * Requires the table to carry `UNIQUE KEY (resource_id, uri)`.
     *
     * @param int    $resourceId The resources.ID this URI belongs to.
     * @param string $uri        The URI path.
     * @return int The resource_uri row ID, or 0 on DB error.
     */
    public function findOrCreate(int $resourceId, string $uri): int
    {
        return $this->upsert([
            'resource_id' => $resourceId,
            'uri'         => $uri,
        ]);
    }
}