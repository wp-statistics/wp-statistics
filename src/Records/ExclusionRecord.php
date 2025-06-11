<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `exclusions` table.
 *
 * Provides methods to retrieve exclusion data based on date and reason.
 * This class relies on BaseRecord for all data access functionality.
 */
class ExclusionRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'exclusions';
}
