<?php

namespace WP_Statistics\Service\ImportExport\Resolvers;

use WP_Statistics\Abstracts\BaseRecord;
use WP_Statistics\Records\ReferrerRecord;

/**
 * Resolver for referrers lookup table.
 *
 * Resolves referrer domain/channel to referrer_id.
 *
 * @since 15.0.0
 */
class ReferrerResolver extends AbstractResolver
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $tableName = 'referrers';

    /**
     * Get the unique key for caching from data.
     *
     * @param array $data Input data (expects 'channel', 'domain' or 'name')
     * @return string Cache key
     */
    protected function getCacheKey(array $data): string
    {
        $channel = $data['channel'] ?? '';
        $domain  = $data['domain'] ?? '';
        $name    = $data['name'] ?? '';

        // Use domain as primary identifier if available
        if (!empty($domain)) {
            return 'domain:' . strtolower(trim($domain));
        }

        // Fall back to channel + name
        if (!empty($channel)) {
            return 'channel:' . strtolower(trim($channel)) . ':' . strtolower(trim($name));
        }

        return '';
    }

    /**
     * Get the lookup criteria for finding existing record.
     *
     * @param array $data Input data
     * @return array Lookup criteria
     */
    protected function getLookupCriteria(array $data): array
    {
        $channel = $data['channel'] ?? '';
        $domain  = $data['domain'] ?? '';

        // Primary lookup by domain
        if (!empty($domain)) {
            return ['domain' => trim($domain)];
        }

        // Fallback to channel lookup
        if (!empty($channel)) {
            $criteria = ['channel' => trim($channel)];

            if (!empty($data['name'])) {
                $criteria['name'] = trim($data['name']);
            }

            return $criteria;
        }

        return [];
    }

    /**
     * Get the data to insert for a new record.
     *
     * @param array $data Input data
     * @return array Insert data
     */
    protected function getInsertData(array $data): array
    {
        $channel = $data['channel'] ?? 'direct';

        if (empty($channel)) {
            $channel = 'direct';
        }

        return [
            'channel' => trim($channel),
            'name'    => $data['name'] ?? null,
            'domain'  => $data['domain'] ?? null,
        ];
    }

    /**
     * Get cache key from a database record.
     *
     * @param object $record Database record
     * @return string Cache key
     */
    protected function getCacheKeyFromRecord(object $record): string
    {
        $domain = $record->domain ?? '';

        if (!empty($domain)) {
            return 'domain:' . strtolower($domain);
        }

        $channel = $record->channel ?? '';
        $name    = $record->name ?? '';

        return 'channel:' . strtolower($channel) . ':' . strtolower($name);
    }

    /**
     * Create the record instance.
     *
     * @return BaseRecord
     */
    protected function createRecord(): BaseRecord
    {
        return new ReferrerRecord();
    }
}
