<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `summary_totals` table.
 *
 * This class relies on BaseRecord for core database operations.
 */
class SummaryTotalRecord extends BaseRecord
{
    /**
     * The table name associated with this record.
     *
     * @var string
     */
    protected $tableName = 'summary_totals';
}
