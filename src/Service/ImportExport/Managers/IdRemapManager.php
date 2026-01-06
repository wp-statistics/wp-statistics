<?php

namespace WP_Statistics\Service\ImportExport\Managers;

/**
 * ID Remap Manager.
 *
 * Manages old_id → new_id mappings during import operations.
 * Used for remapping foreign key references when restoring backups.
 *
 * @since 15.0.0
 */
class IdRemapManager
{
    /**
     * ID mappings per table.
     *
     * @var array<string, array<int, int>>
     */
    private $mappings = [];

    /**
     * Add a mapping for a table.
     *
     * @param string $table Table name
     * @param int    $oldId Original ID from backup
     * @param int    $newId New ID in database
     * @return self
     */
    public function addMapping(string $table, int $oldId, int $newId): self
    {
        if (!isset($this->mappings[$table])) {
            $this->mappings[$table] = [];
        }

        $this->mappings[$table][$oldId] = $newId;

        return $this;
    }

    /**
     * Add multiple mappings for a table.
     *
     * @param string $table    Table name
     * @param array  $mappings Array of oldId => newId mappings
     * @return self
     */
    public function addMappings(string $table, array $mappings): self
    {
        foreach ($mappings as $oldId => $newId) {
            $this->addMapping($table, (int)$oldId, (int)$newId);
        }

        return $this;
    }

    /**
     * Get the new ID for an old ID.
     *
     * @param string $table Table name
     * @param int    $oldId Original ID from backup
     * @return int|null New ID or null if not found
     */
    public function getNewId(string $table, int $oldId): ?int
    {
        return $this->mappings[$table][$oldId] ?? null;
    }

    /**
     * Check if a mapping exists.
     *
     * @param string $table Table name
     * @param int    $oldId Original ID
     * @return bool
     */
    public function hasMapping(string $table, int $oldId): bool
    {
        return isset($this->mappings[$table][$oldId]);
    }

    /**
     * Get all mappings for a table.
     *
     * @param string $table Table name
     * @return array<int, int> oldId => newId mappings
     */
    public function getMappings(string $table): array
    {
        return $this->mappings[$table] ?? [];
    }

    /**
     * Get all table names with mappings.
     *
     * @return array<string>
     */
    public function getTables(): array
    {
        return array_keys($this->mappings);
    }

    /**
     * Remap a row's foreign key references.
     *
     * @param array $row          Row data
     * @param array $fkDefinitions Foreign key definitions [column => table]
     * @return array Row with remapped IDs
     */
    public function remapRow(array $row, array $fkDefinitions): array
    {
        foreach ($fkDefinitions as $column => $table) {
            if (isset($row[$column]) && !empty($row[$column])) {
                $oldId = (int)$row[$column];
                $newId = $this->getNewId($table, $oldId);

                if ($newId !== null) {
                    $row[$column] = $newId;
                } else {
                    // If no mapping found, set to null to avoid FK violations
                    $row[$column] = null;
                }
            }
        }

        return $row;
    }

    /**
     * Clear all mappings.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->mappings = [];
        return $this;
    }

    /**
     * Clear mappings for a specific table.
     *
     * @param string $table Table name
     * @return self
     */
    public function clearTable(string $table): self
    {
        unset($this->mappings[$table]);
        return $this;
    }

    /**
     * Get total mapping count.
     *
     * @return int
     */
    public function getTotalCount(): int
    {
        $count = 0;

        foreach ($this->mappings as $tableMappings) {
            $count += count($tableMappings);
        }

        return $count;
    }

    /**
     * Get mapping count for a table.
     *
     * @param string $table Table name
     * @return int
     */
    public function getTableCount(string $table): int
    {
        return count($this->mappings[$table] ?? []);
    }

    /**
     * Serialize mappings to array (for persistence).
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->mappings;
    }

    /**
     * Load mappings from array.
     *
     * @param array $mappings Serialized mappings
     * @return self
     */
    public function fromArray(array $mappings): self
    {
        $this->mappings = $mappings;
        return $this;
    }
}
