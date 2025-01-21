<?php

namespace WP_Statistics\Service\Admin\Database\Operations;

use RuntimeException;

/**
 * Handles data insertion and migration between database tables.
 *
 * This class provides methods to migrate data from a source table to a target
 * table with customizable mappings and transactional support.
 */
class Insert extends AbstractTableOperation
{
    /**
     * The source table for data migration.
     * 
     * @var string
     */
    protected $sourceTable;

    /**
     * Set the source table (old table) for the operation.
     *
     * @param string $sourceTable
     * @return $this
     */
    public function setSourceTable(string $sourceTable)
    {
        $this->sourceTable = $sourceTable;
        return $this;
    }

    /**
     * Execute the table insert operation, migrating data from the source table.
     *
     * @return void
     * @throws RuntimeException
     */
    public function execute()
    {
        try {
            $this->ensureConnection();
            $this->validateTableName();
            $this->validateArgs();
            $this->setFullTableName();

            $this->transactionHandler->executeInTransaction([$this, 'migrateData']);
        } catch (\Exception $e) {
            throw new RuntimeException(
                sprintf("Failed to migrate data to table `%s`: %s", $this->tableName, $e->getMessage())
            );
        }
    }

    /**
     * Migrate data operation to be executed within a transaction.
     *
     * @return void
     * @throws RuntimeException
     */
    public function migrateData()
    {
        if (empty($this->sourceTable)) {
            throw new RuntimeException("Source table is not specified for migration.");
        }

        $mapping = $this->args['mapping'] ?? [];
        if (empty($mapping)) {
            throw new RuntimeException("Mapping is required for migration.");
        }

        $batchSize = $this->args['batch_size'] ?? 50;
        $offset = $this->args['offset'] ?? 0;

        $sourceColumns = implode(', ', array_values($mapping));

        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT $sourceColumns FROM {$this->wpdb->prefix}{$this->sourceTable} LIMIT %d OFFSET %d",
                $batchSize,
                $offset
            ),
            ARRAY_A
        );

        if ($rows === null || $rows === false) {
            throw new RuntimeException("Failed to fetch rows: {$this->wpdb->last_error}");
        }

        foreach ($rows as $row) {
            $mappedRow = [];
            foreach ($mapping as $targetColumn => $sourceColumn) {
                if (isset($row[$sourceColumn])) {
                    $mappedRow[$targetColumn] = $row[$sourceColumn];
                }
            }

            if (!empty($mappedRow)) {
                $result = $this->wpdb->insert($this->fullName, $mappedRow);

                if ($result === false) {
                    throw new RuntimeException("Failed to insert data: {$this->wpdb->last_error}");
                }
            }
        }
    }
}
