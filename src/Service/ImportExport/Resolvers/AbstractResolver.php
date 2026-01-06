<?php

namespace WP_Statistics\Service\ImportExport\Resolvers;

use WP_Statistics\Abstracts\BaseRecord;
use WP_Statistics\Service\ImportExport\Contracts\ResolverInterface;
use WP_Statistics\Utils\Query;

/**
 * Abstract base class for lookup resolvers.
 *
 * Provides common functionality for find-or-create pattern with in-memory caching.
 * Concrete resolvers extend this class and define their specific lookup logic.
 *
 * @since 15.0.0
 */
abstract class AbstractResolver implements ResolverInterface
{
    /**
     * In-memory cache for resolved IDs.
     *
     * @var array<string, int>
     */
    protected $cache = [];

    /**
     * Record instance for database operations.
     *
     * @var BaseRecord|null
     */
    protected $record = null;

    /**
     * Table name (without prefix).
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * Get the unique key for caching from data.
     *
     * @param array $data Input data
     * @return string Cache key
     */
    abstract protected function getCacheKey(array $data): string;

    /**
     * Get the lookup criteria for finding existing record.
     *
     * @param array $data Input data
     * @return array Associative array of column => value for lookup
     */
    abstract protected function getLookupCriteria(array $data): array;

    /**
     * Get the data to insert for a new record.
     *
     * @param array $data Input data
     * @return array Associative array of column => value for insert
     */
    abstract protected function getInsertData(array $data): array;

    /**
     * Resolve data to a foreign key ID.
     *
     * If record exists, returns existing ID.
     * If not exists, creates new record and returns new ID.
     *
     * @param array $data Data to resolve
     * @return int|null The resolved ID, or null if unable to resolve
     */
    public function resolve(array $data): ?int
    {
        // Generate cache key
        $cacheKey = $this->getCacheKey($data);

        if (empty($cacheKey)) {
            return null;
        }

        // Check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        // Try to find existing record
        $criteria = $this->getLookupCriteria($data);

        if (empty($criteria)) {
            return null;
        }

        $record = $this->getRecord();
        $id     = $record->getId($criteria);

        if ($id) {
            $this->cache[$cacheKey] = (int)$id;
            return (int)$id;
        }

        // Create new record
        $insertData = $this->getInsertData($data);

        if (empty($insertData)) {
            return null;
        }

        $id = $record->insert($insertData);

        if ($id) {
            $this->cache[$cacheKey] = (int)$id;
            return (int)$id;
        }

        return null;
    }

    /**
     * Resolve multiple records in batch.
     *
     * More efficient than calling resolve() multiple times for large datasets.
     *
     * @param array<array> $dataArray Array of data arrays to resolve
     * @return array<int|null> Array of resolved IDs (same order as input)
     */
    public function resolveBatch(array $dataArray): array
    {
        $results = [];

        // First pass: check cache and collect uncached items
        $uncached      = [];
        $uncachedIndex = [];

        foreach ($dataArray as $index => $data) {
            $cacheKey = $this->getCacheKey($data);

            if (empty($cacheKey)) {
                $results[$index] = null;
                continue;
            }

            if (isset($this->cache[$cacheKey])) {
                $results[$index] = $this->cache[$cacheKey];
            } else {
                $uncached[$index]      = $data;
                $uncachedIndex[$index] = $cacheKey;
            }
        }

        // Resolve uncached items
        foreach ($uncached as $index => $data) {
            $results[$index] = $this->resolve($data);
        }

        // Sort by original index
        ksort($results);

        return $results;
    }

    /**
     * Get resolved ID from cache without creating new record.
     *
     * @param string $key Cache key
     * @return int|null Cached ID or null if not in cache
     */
    public function getCached(string $key): ?int
    {
        return $this->cache[$key] ?? null;
    }

    /**
     * Clear the in-memory cache.
     *
     * Useful for long-running processes to free memory.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Get the target table name for this resolver.
     *
     * @return string Table name
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Pre-warm the cache with existing records.
     *
     * Loads common/frequent records into cache before import.
     *
     * @param int $limit Max records to load (default: 1000)
     * @return int Number of records loaded into cache
     */
    public function warmCache(int $limit = 1000): int
    {
        $records = Query::select('*')
            ->from($this->tableName)
            ->limit($limit)
            ->getAll();

        $count = 0;

        foreach ($records as $record) {
            $cacheKey = $this->getCacheKeyFromRecord($record);

            if (!empty($cacheKey) && !empty($record->ID)) {
                $this->cache[$cacheKey] = (int)$record->ID;
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get cache key from a database record.
     *
     * Used by warmCache() to populate cache from existing records.
     *
     * @param object $record Database record
     * @return string Cache key
     */
    protected function getCacheKeyFromRecord(object $record): string
    {
        // Default implementation - override in subclasses if needed
        return '';
    }

    /**
     * Get the record instance (lazy loaded).
     *
     * @return BaseRecord
     */
    protected function getRecord(): BaseRecord
    {
        if ($this->record === null) {
            $this->record = $this->createRecord();
        }

        return $this->record;
    }

    /**
     * Create the record instance.
     *
     * Override in subclasses to return specific record type.
     *
     * @return BaseRecord
     */
    abstract protected function createRecord(): BaseRecord;

    /**
     * Get current cache size.
     *
     * @return int Number of cached entries
     */
    public function getCacheSize(): int
    {
        return count($this->cache);
    }
}
