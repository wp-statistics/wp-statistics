<?php

namespace WP_Statistics\Service\Admin\Database\Operations;

/**
 * Handles updating database table structures.
 *
 * This class provides functionality to modify or add columns in a table
 * using ALTER statements, ensuring transactional integrity.
 */
class Update extends AbstractTableOperation
{
    /**
     * Execute the table update operation.
     *
     * @return void
     * @throws \RuntimeException
     */
    public function execute()
    {
        try {
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
     * @return array
     */
    private function buildAlterStatements()
    {
        $alters = [];

        foreach ($this->args as $column => $definition) {
            $columnExists = $this->wpdb->get_results(
                $this->wpdb->prepare(
                    "SHOW COLUMNS FROM `{$this->fullName}` LIKE %s",
                    $column
                )
            );

            if ($columnExists) {
                $alters[] = sprintf("MODIFY COLUMN `%s` %s", $column, $definition);
            } else {
                $alters[] = sprintf("ADD COLUMN `%s` %s", $column, $definition);
            }
        }

        return $alters;
    }
}