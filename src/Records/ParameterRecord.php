<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;

/**
 * Handles database interactions for the `parameters` table.
 *
 * This class extends the BaseRecord and provides convenient methods
 * to retrieve parameter data based on common filters such as session ID,
 * resource ID, view ID, and parameter key.
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

    /**
     * Get all records by view ID.
     *
     * @param int $viewId
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByViewId($viewId)
    {
        return empty($viewId) ? [] : $this->getAll(['view_id' => $viewId]);
    }

    /**
     * Get all records by parameter key (e.g., 'utm_source').
     *
     * @param string $parameter
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByKey($parameter)
    {
        return empty($parameter) ? [] : $this->getAll(['parameter' => $parameter]);
    }

    /**
     * Get the value of a specific parameter by composite keys.
     *
     * @param int $sessionId
     * @param int $resourceId
     * @param int $viewId
     * @param string $parameter
     * @return string|null
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getValue($sessionId, $resourceId, $viewId, $parameter)
    {
        if (empty($sessionId) || empty($resourceId) || empty($viewId) || empty($parameter)) {
            return null;
        }

        $row = $this->get([
            'session_id'  => $sessionId,
            'resource_id' => $resourceId,
            'view_id'     => $viewId,
            'parameter'   => $parameter,
        ]);

        return $row ? $row->value : null;
    }
}
