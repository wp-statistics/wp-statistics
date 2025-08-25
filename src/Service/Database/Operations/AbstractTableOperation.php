<?php

namespace WP_Statistics\Service\Database\Operations;

use RuntimeException;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\AbstractDatabaseOperation;

/**
 * Defines a foundation for operations on database tables.
 *
 * This abstract class provides core methods for configuring and interacting
 * with specific database tables, such as setting the table name.
 */
abstract class AbstractTableOperation extends AbstractDatabaseOperation
{
    /**
     * The operation result buffer for this instance (per-request).
     *
     * @var array
     */
    protected $result = [];

    /**
     * Sets the name of the table for the operation.
     *
     * @param string $name The name of the table.
     * @return $this
     */
    public function setName(string $name)
    {
        $this->tableName = $name;
        return $this;
    }

    /**
     * Clear per-instance memoized results for this operation.
     *
     * @return $this
     */
    public function updateCache()
    {
        if (empty($this->result)) {
            return $this;
        }

        $this->setFullTableName();

        if (!isset($this->result[$this->fullName])) {
            return $this;
        }

        unset($this->result[$this->fullName]);

        return $this;
    }

    /**
     * Sets a runtime error based on the migration status details.
     *
     * @return void
     * @throws RuntimeException If the migration status is 'failed'.
     */
    public function setRunTimeError()
    {
        $details = Option::getOptionGroup('db', 'migration_status_detail', null);

        if (empty($details['status'])) {
            return;
        }

        if ($details['status'] === 'failed') {
            throw new RuntimeException($details['message']);
        }

        return;
    }
}
