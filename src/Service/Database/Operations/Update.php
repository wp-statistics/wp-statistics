<?php

namespace WP_Statistics\Service\Database\Operations;

use WP_Statistics\Service\Database\DatabaseFactory;

/**
 * Handles updating database table structures.
 *
 * This class provides functionality to modify, add, rename, or drop columns in a table
 * using ALTER statements, ensuring transactional integrity.
 */
class Update extends AbstractTableOperation
{
    /**
     * Cached columns for the table.
     *
     * @var array
     */
    private $cachedColumns = [];

    /**
     * Execute the table update operation.
     *
     * @return void
     * @throws \RuntimeException
     */
    public function execute()
    {
        try {
            $this->ensureConnection();
            $this->validateTableName();
            $this->validateArgs();
            $this->setFullTableName();

            $this->transactionHandler->executeInTransaction([$this, 'updateTable']);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf("Failed to update table `%s`: %s", $this->tableName, $e->getMessage())
            );
        }
    }

    /**
     * Update table operation to be executed in transaction.
     *
     * @return void
     * @throws \RuntimeException
     */
    public function updateTable()
    {
        $alters = $this->buildAlterStatements();

        if (!empty($alters)) {
            $sql = sprintf(
                "ALTER TABLE `%s` %s",
                $this->fullName,
                implode(", ", $alters)
            );

            if ($this->wpdb->query($sql) === false) {
                throw new \RuntimeException(
                    sprintf('MySQL Error: %s', $this->wpdb->last_error)
                );
            }
        }
    }

    /**
     * Build the ALTER statements for each column.
     *
     * Supports adding, modifying, renaming, and dropping columns.
     *
     * @return array
     */
    private function buildAlterStatements()
    {
        $alters = [];

        foreach ($this->args as $operation => $details) {
            switch ($operation) {
                case 'add':
                    foreach ($details as $column => $definition) {
                        if (!$this->columnExists($column)) {
                            $alters[] = sprintf("ADD COLUMN `%s` %s", $column, $definition);
                        }
                    }
                    break;

                case 'modify':
                    foreach ($details as $column => $definition) {
                        if ($this->columnExists($column)) {
                            $alters[] = sprintf("MODIFY COLUMN `%s` %s", $column, $definition);
                        }
                    }
                    break;

                case 'rename':
                    foreach ($details as $oldColumn => $renameDetails) {
                        if ($this->columnExists($oldColumn)) {
                            $alters[] = sprintf(
                                "CHANGE `%s` `%s` %s",
                                $oldColumn,
                                $renameDetails['new_name'],
                                $renameDetails['definition']
                            );
                        }
                    }
                    break;

                case 'drop':
                    foreach ($details as $column) {
                        if ($this->columnExists($column)) {
                            $alters[] = sprintf("DROP COLUMN `%s`", $column);
                        }
                    }
                    break;

                case 'foreign':
                    foreach ($details as $foreignKeyName => $foreignDetails) {
                        $uniqueForeignKeyName = $foreignKeyName . '_' . uniqid();

                        $alters[] = sprintf(
                            "ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON DELETE %s ON UPDATE %s",
                            $uniqueForeignKeyName,
                            $foreignDetails['column'],
                            $foreignDetails['referenced_table'],
                            $foreignDetails['referenced_column'],
                            $foreignDetails['on_delete'],
                            $foreignDetails['on_update']
                        );
                    }
                    break;
            }
        }

        return $alters;
    }

    /**
     * Check if a column exists in the table.
     *
     * @param string $column
     * @return bool
     */
    private function columnExists(string $column): bool
    {
        if (empty($this->cachedColumns)) {
            $this->cacheTableColumns();
        }

        return in_array($column, $this->cachedColumns, true);
    }

    /**
     * Cache the columns of the table for faster lookups.
     *
     * @return void
     */
    private function cacheTableColumns(): void
    {
        $inspect = DatabaseFactory::table('inspect')
            ->setName($this->tableName)
            ->execute();

        if (!$inspect->getResult()) {
            throw new \RuntimeException(
                sprintf('Table does not exist')
            );
        }

        $results = $this->wpdb->get_results(
            sprintf("SHOW COLUMNS FROM `%s`", $this->fullName),
            ARRAY_A
        );

        $this->cachedColumns = array_column($results, 'Field');
    }
}
