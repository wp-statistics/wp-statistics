<?php

namespace WP_Statistics\Service\ImportExport\Contracts;

/**
 * Interface for lookup resolvers.
 *
 * Resolvers handle the "find-or-create" pattern for foreign key lookups.
 * They maintain an in-memory cache to avoid repeated database queries.
 *
 * Example: CountryResolver resolves 'US' -> country_id
 *
 * @since 15.0.0
 */
interface ResolverInterface
{
    /**
     * Resolve data to a foreign key ID.
     *
     * If record exists, returns existing ID.
     * If not exists, creates new record and returns new ID.
     *
     * @param array $data Data to resolve (e.g., ['code' => 'US'] for country)
     * @return int|null The resolved ID, or null if unable to resolve
     */
    public function resolve(array $data): ?int;

    /**
     * Resolve multiple records in batch.
     *
     * More efficient than calling resolve() multiple times.
     *
     * @param array<array> $dataArray Array of data arrays to resolve
     * @return array<int|null> Array of resolved IDs (same order as input)
     */
    public function resolveBatch(array $dataArray): array;

    /**
     * Get resolved ID from cache without creating new record.
     *
     * @param string $key Cache key (e.g., country code)
     * @return int|null Cached ID or null if not in cache
     */
    public function getCached(string $key): ?int;

    /**
     * Clear the in-memory cache.
     *
     * Useful for long-running processes to free memory.
     *
     * @return void
     */
    public function clearCache(): void;

    /**
     * Get the target table name for this resolver.
     *
     * @return string Table name (e.g., 'countries', 'device_browsers')
     */
    public function getTableName(): string;

    /**
     * Pre-warm the cache with existing records.
     *
     * Loads common/frequent records into cache before import.
     *
     * @param int $limit Max records to load (default: 1000)
     * @return int Number of records loaded into cache
     */
    public function warmCache(int $limit = 1000): int;
}
