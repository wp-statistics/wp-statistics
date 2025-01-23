<?php

namespace WP_Statistics\Service\Admin\Database\Operations;

/**
 * Handles the dropping of database tables.
 *
 * This class provides methods to safely execute table drop operations
 * while ensuring transactional integrity.
 */
class Drop extends AbstractTableOperation
{
    /**
     * Execute the table drop operation.
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

            return $this->transactionHandler->executeInTransaction([$this, 'dropTable']);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf("Failed to drop table `%s`: %s", $this->tableName, $e->getMessage())
            );
        }
    }

    /**
     * Drop table operation to be executed in transaction.
     *
     * @return self
     * @throws \RuntimeException
     */
    public function dropTable()
    {
        $sql = sprintf("DROP TABLE IF EXISTS `%s`", $this->fullName);

        if ($this->wpdb->query($sql) === false) {
            throw new \RuntimeException(
                sprintf('MySQL Error: %s', $this->wpdb->last_error)
            );
        }

        return $this;
    }
}
