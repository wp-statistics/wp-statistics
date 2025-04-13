<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `summary` table.
 *
 * Provides methods to retrieve summary data by resource ID.
 */
class ReportRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'reports';

    /**
     * Get all summary records by resource ID.
     *
     * @param int $resourceId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByResourceId($resourceId)
    {
        return empty($resourceId) ? [] : $this->getAll(['resource_id' => $resourceId]);
    }
}
