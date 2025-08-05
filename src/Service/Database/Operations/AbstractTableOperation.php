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
     * Sets a runtime error based on the migration status details.
     *
     * @throws RuntimeException If the migration status is 'failed'.
     * @return void
     */
    public function setRunTimeError() {
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
