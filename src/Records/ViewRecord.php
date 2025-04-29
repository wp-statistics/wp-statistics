<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `views` table.
 *
 * This class provides convenience methods for retrieving view records
 * by common filters such as session ID or resource ID.
 */
class ViewRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'views';
}
