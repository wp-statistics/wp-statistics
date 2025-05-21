<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `summary` table.
 *
 * This class relies on BaseRecord for core database operations.
 */
class SummaryRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'summary';
}
