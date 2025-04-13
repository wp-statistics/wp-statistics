<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `visitors` table.
 *
 * This class relies on BaseRecord for core database operations.
 */
class VisitorRecord extends BaseRecord
{
    /**
     * Sets the raw table name for this record.
     *
     * @return void
     */
    protected function setTableName()
    {
        $this->tableName = 'visitors';
    }
}
