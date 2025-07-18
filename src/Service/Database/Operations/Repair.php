<?php

namespace WP_Statistics\Service\Database\Operations;

/**
 * Handles database repair operations.
 *
 * This class provides functionality to fix database structure issues,
 * particularly focusing on adding or modifying columns and indexes
 * based on the schema definition.
 */
class Repair extends AbstractTableOperation
{
    /**
     * Execute the repair operation.
     *
     * @return self
     * @throws \RuntimeException
     */
    public function execute()
    {
        try {
            $this->ensureConnection();
            $this->validateTableName();
            $this->validateArgs();
            $this->setFullTableName();

            return $this->transactionHandler->executeInTransaction([$this, 'repairTable']);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf("Failed to repair table `%s`: %s", $this->tableName, $e->getMessage())
            );
        }
    }

    /**
     * Repair table operation to be executed in transaction.
     *
     * @return self
     * @throws \RuntimeException
     */
    public function repairTable()
    {
        if (!empty($this->args['column']) && !empty($this->args['definition'])) {
            $this->addColumn($this->args['column'], $this->args['definition']);
        }

        return $this;
    }

    /**
     * Add a new column to the table.
     *
     * @param string $columnName The name of the column to add
     * @param string $definition The column definition
     * @throws \RuntimeException If the operation fails
     */
    private function addColumn($columnName, $definition)
    {
        $sql = sprintf(
            "ALTER TABLE `%s` ADD COLUMN `%s` %s",
            $this->fullName,
            $columnName,
            $definition
        );

        if ($this->wpdb->query($sql) === false) {
            throw new \RuntimeException(
                sprintf('MySQL Error: %s', $this->wpdb->last_error)
            );
        }

        if (!empty($this->args['indexDefinition'])) {
            $this->addIndex($this->args['indexDefinition']);
        }
    }

    /**
     * Add a new index to the table.
     *
     * @param string $indexDefinition The complete index definition
     * @throws \RuntimeException If the operation fails
     */
    private function addIndex($indexDefinition)
    {
        $sql = sprintf(
            "ALTER TABLE `%s` ADD %s",
            $this->fullName,
            $indexDefinition
        );

        if ($this->wpdb->query($sql) === false) {
            throw new \RuntimeException(
                sprintf('MySQL Error: %s', $this->wpdb->last_error)
            );
        }
    }

    /**
     * Validate the operation arguments.
     *
     * @return void
     * @throws \RuntimeException
     */
    protected function validateArgs()
    {
        if (empty($this->args)) {
            throw new \RuntimeException('Arguments are required for repair operation');
        }

        $hasColumn = isset($this->args['column'], $this->args['definition']);
        $hasIndex  = isset($this->args['indexDefinition']);

        if (!$hasColumn && !$hasIndex) {
            throw new \RuntimeException('Either column or index definition is required');
        }

    }
}