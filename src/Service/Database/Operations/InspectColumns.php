<?php

namespace WP_Statistics\Service\Database\Operations;

use WP_Statistics\Service\Database\DatabaseFactory;

/**
 * Handles inspection of database table structure.
 *
 * This class provides functionality to inspect column and index definitions
 * from database tables.
 */
class InspectColumns extends AbstractTableOperation
{
    /**
     * The operation result.
     *
     * @var array|null
     */
    protected $result;

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

            $this->result = $this->transactionHandler->executeInTransaction([$this, 'inspectStructure']);
            return $this;
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf("Failed to inspect table `%s`: %s", $this->tableName, $e->getMessage())
            );
        }
    }

    /**
     * Structure inspection operation to be executed in transaction.
     *
     * @return array Array of column and index information
     * @throws \RuntimeException
     */
    public function inspectStructure()
    {
        // Check if table exists first
        $tableExists = DatabaseFactory::table('inspect')
            ->setName($this->tableName)
            ->execute()
            ->getResult();

        if (!$tableExists) {
            throw new \RuntimeException(
                sprintf("Table `%s` does not exist", $this->tableName)
            );
        }

        // Get columns
        $columns = $this->wpdb->get_results(
            sprintf("SHOW COLUMNS FROM `%s`", $this->fullName),
            'ARRAY_A'
        );

        if ($columns === false) {
            throw new \RuntimeException(
                sprintf('MySQL Error while fetching columns: %s', $this->wpdb->last_error)
            );
        }

        return $columns;
    }

    /**
     * Get the operation result.
     *
     * @return array|null Array of column and index information or null if not executed
     */
    public function getResult()
    {
        return $this->result;
    }
} 