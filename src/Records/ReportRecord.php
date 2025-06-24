<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `reports` table.
 *
 * Extends the BaseRecord class to perform common database operations.
 *
 * @since 15.0.0
 */
class ReportRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'reports';
}
