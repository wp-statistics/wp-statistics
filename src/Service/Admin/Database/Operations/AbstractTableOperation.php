<?php

namespace WP_Statistics\Service\Admin\Database\Operations;

use WP_Statistics\Service\Admin\Database\AbstractDatabaseOperation;

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
}
