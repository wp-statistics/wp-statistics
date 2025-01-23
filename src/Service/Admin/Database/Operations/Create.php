<?php

namespace WP_Statistics\Service\Admin\Database\Operations;

/**
 * Handles the creation of database tables.
 *
 * This class defines methods to execute table creation operations, 
 * including generating SQL queries and ensuring transactional integrity.
 */
class Create extends AbstractTableOperation
{
    /**
     * Execute the table creation operation.
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

            return $this->transactionHandler->executeInTransaction([$this, 'createTable']);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf("Failed to create table `%s`: %s", $this->tableName, $e->getMessage())
            );
        }
    }

    /**
     * Create table operation to be executed in transaction.
     *
     * @return self
     * @throws \RuntimeException
     */
    public function createTable()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = $this->buildCreateTableSql();
        $result = $this->wpdb->query($sql);

        if ($result === false) {
            throw new \RuntimeException(
                sprintf('Failed to create table. MySQL Error: %s', $this->wpdb->last_error)
            );
        }

        return $this;
    }

    /**
     * Build the CREATE TABLE SQL query.
     *
     * @return string
     */
    private function buildCreateTableSql()
    {
        $charset_collate = $this->wpdb->get_charset_collate();
        $columns = $this->args['columns'] ?? [];
        $constraints = $this->args['constraints'] ?? [];

        $columnsSql = array_map(function ($columnName, $definition) {
            return sprintf("`%s` %s", $columnName, $definition);
        }, array_keys($columns), $columns);

        $allDefinitions = array_merge($columnsSql, $constraints);

        return sprintf(
            "CREATE TABLE IF NOT EXISTS `%s` (\n%s\n) %s;",
            $this->fullName,
            implode(",\n", $allDefinitions),
            $charset_collate
        );
    }
}
