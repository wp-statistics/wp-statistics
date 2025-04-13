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
     * Sets the raw table name for this record.
     *
     * @return void
     */
    protected function setTableName()
    {
        $this->tableName = 'views';
    }

    /**
     * Get all records by session ID.
     *
     * @param int $sessionId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllBySessionId($sessionId)
    {
        return empty($sessionId) ? [] : $this->getAll(['session_id' => $sessionId]);
    }

    /**
     * Get all records by resource ID.
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
