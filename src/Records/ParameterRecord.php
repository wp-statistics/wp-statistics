<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `parameters` table.
 *
 * Parameters are stored at the session level for first-touch attribution.
 *
 * @since 15.0.0
 */
class ParameterRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'parameters';

    /**
     * Get all records by session ID.
     *
     * @param int $sessionId
     * @return array
     */
    public function getAllBySessionId($sessionId)
    {
        return empty($sessionId) ? [] : $this->getAll(['session_id' => $sessionId]);
    }
}
