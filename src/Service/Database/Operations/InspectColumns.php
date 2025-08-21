<?php

namespace WP_Statistics\Service\Database\Operations;

/**
 * Handles inspection of database table structure.
 *
 * This class provides functionality to inspect column and index definitions
 * from database tables.
 */
class InspectColumns extends AbstractTableOperation
{
    /**
     * Execute the structure inspection operation.
     *
     * @return self
     * @throws \RuntimeException
     */
    public function execute()
    {
        try {
            $this->ensureConnection();
            $this->validateTableName();
            $this->setFullTableName();
            $this->inspectStructure();

            return $this;
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf("Failed to inspect table `%s`: %s", $this->tableName, $e->getMessage())
            );
        }
    }

    /**
     * Structure inspection operation.
     *
     * @return array Array of column information
     * @throws \RuntimeException
     */
    public function inspectStructure()
    {
        if (isset($this->result[$this->fullName])) {
            return $this->result[$this->fullName];
        }

        $columns = $this->wpdb->get_results(
            sprintf('SHOW COLUMNS FROM `%s`', $this->fullName),
            'ARRAY_A'
        );

        if ($columns === null) {
            throw new \RuntimeException(
                sprintf('MySQL Error while fetching columns: %s', $this->wpdb->last_error)
            );
        }

        $this->result[$this->fullName] = $columns;

        return $this->result[$this->fullName];
    }

    /**
     * Get the operation result.
     *
     * @return array|null Array of column information or null if not executed
     */
    public function getResult()
    {
        return isset($this->result[$this->fullName]) ? $this->result[$this->fullName] : null;
    }
} 