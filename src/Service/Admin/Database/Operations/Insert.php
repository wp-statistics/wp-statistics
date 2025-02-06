<?php

namespace WP_Statistics\Service\Admin\Database\Operations;

use RuntimeException;
use WP_STATISTICS\Option;

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
     * The source table name (with WordPress prefix).
     *
     * @var string
     */
    protected $prefixedSourceTable;

    /**
     * Set the source table (old table) for the operation.
     *
     * @param string $sourceTable
     * @return $this
     */
    public function setSourceTable(string $sourceTable)
    {
        $this->sourceTable         = $sourceTable;
        $this->prefixedSourceTable = $this->wpdb->prefix . 'statistics_' . $sourceTable;

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
            Option::saveOptionGroup('migration_status_detail', [
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 'db');

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
        $distinctFields = $this->args['distinct_fields'] ?? [];
        $sourceTableSet = $this->args['source_table_set'] ?? [];

        if (empty($mapping)) {
            throw new RuntimeException("Mapping is required for migration.");
        }

        $batchSize = $this->args['batch_size'] ?? 50;
        $offset = $this->args['offset'] ?? 0;

        if (!empty($distinctFields)) {
            $mapping = 'DISTINCT ' . implode(', ', $mapping) . ', ';
        }

        // Prepare the columns for fetching data from the source table
        $sourceColumns = implode(', ', array_values($mapping));
        $distinctQuery = '';

        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT $distinctQuery $sourceColumns FROM {$this->prefixedSourceTable} LIMIT %d OFFSET %d",
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

            // Check if the entry already exists in the target table (resources) based on distinct_fields
            if (!empty($distinctFields)) {
                $conditions = [];
                foreach ($distinctFields as $field) {
                    $conditions[] = $this->wpdb->prepare("`$field` = %s", $row[$field]);
                }

                $conditionQuery = implode(' AND ', $conditions);
                $exists = $this->wpdb->get_var(
                    "SELECT COUNT(*) FROM {$this->fullName} WHERE $conditionQuery"
                );

                if ($exists > 0) {
                    continue; // Skip this row if it already exists
                }
            }

            if (!empty($mappedRow)) {
                // Insert into target table (resources)
                $result = $this->wpdb->insert($this->fullName, $mappedRow);

                if ($result === false) {
                    throw new RuntimeException("Failed to insert data: {$this->wpdb->last_error}");
                }

                if (!empty($sourceTableSet)) {
                    $toColumn   = $sourceTableSet['to'];
                    $fromColumn = $sourceTableSet['from'];
                    $insertedId = $this->wpdb->insert_id;

                    $updateResult = $this->wpdb->update(
                        $this->prefixedSourceTable,
                        [$toColumn => $insertedId],
                        [$fromColumn => $row[$fromColumn]]
                    );

                    if ($updateResult === false) {
                        throw new RuntimeException("Failed to update source table: {$this->wpdb->last_error}");
                    }
                }
            }
        }
    }
}
