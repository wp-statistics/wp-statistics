<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Record model for the `resolutions` table.
 *
 * This class relies on BaseRecord for all data access functionality.
 */
class ResolutionRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'resolutions';
}
