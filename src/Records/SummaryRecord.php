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
     * Sets the raw table name for this record.
     *
     * @return void
     */
    protected function setTableName()
    {
        $this->tableName = 'summary';
    }
}
