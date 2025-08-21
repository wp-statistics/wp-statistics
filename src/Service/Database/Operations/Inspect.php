<?php

namespace WP_Statistics\Service\Database\Operations;

/**
 * Handles inspection of database tables.
 *
 * This class provides functionality to check the existence of a specified table
 * in the database.
 */
class Inspect extends AbstractTableOperation
{
    /**
     * Executes the table inspection operation.
     *
     * @return $this
     */
    public function execute()
    {
        $this->validateTableName();
        $this->setFullTableName();
        $this->inspectTable();

        return $this;
    }

    /**
     * Determines whether the table exists in the database.
     *
     * @return bool True if the table exists; false otherwise.
     */
    private function inspectTable()
    {
        if (isset($this->result[$this->fullName])) {
            return $this->result[$this->fullName];
        }

        $query = $this->wpdb->prepare('SHOW TABLES LIKE %s', $this->fullName);
        $value = $this->wpdb->get_var($query);

        $exists = !empty($value);

        $this->result[$this->fullName] = $exists;

        return $exists;
    }

    /**
     * Retrieves the result of the table inspection.
     *
     * @return bool|null True if table exists, false if not, or null if not executed yet.
     */
    public function getResult()
    {
        return isset($this->result[$this->fullName]) ? $this->result[$this->fullName] : null;
    }
}
