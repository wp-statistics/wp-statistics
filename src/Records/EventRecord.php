<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `events` table.
 *
 * This class relies on BaseRecord for all data access functionality.
 *
 * @since 15.0.0
 */
class EventRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'events';
}