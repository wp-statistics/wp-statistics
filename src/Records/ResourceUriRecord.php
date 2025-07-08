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
}