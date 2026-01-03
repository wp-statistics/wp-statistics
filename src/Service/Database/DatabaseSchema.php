<?php

namespace WP_Statistics\Service\Database;

/**
 * Database Schema Registry for WP Statistics v15.
 *
 * Provides table name management with caching for optimal performance.
 * Replaces legacy WP_STATISTICS\DB class.
 *
 * @since 15.0.0
 */
class DatabaseSchema
{
    /**
     * Table name prefix pattern.
     */
    private const TABLE_PATTERN = '[prefix]statistics_[name]';

    /**
     * Core WP Statistics tables.
     *
     * @var array
     */
    private static $coreTables = [
        'useronline',
        'visitor',
        'exclusions',
        'pages',
        'historical',
        'visitor_relationships',
        'resources',
        'resource_uris',
        'parameters',
        'views',
        'countries',
        'cities',
        'device_types',
        'device_browser_versions',
        'device_browsers',
        'device_oss',
        'resolutions',
        'languages',
        'timezones',
        'referrers',
        'visitors',
        'sessions',
        'reports',
        'summary',
        'summary_totals',
    ];

    /**
     * Addon tables.
     *
     * @var array
     */
    private static $addonTables = [
        'events',      // Data Plus
        'ar_outbox',   // Advanced Reporting
        'campaigns',   // Marketing
        'goals',       // Marketing
    ];

    /**
     * Cached table names.
     *
     * @var array|null
     */
    private static $tableCache = null;

    /**
     * Cached prefix.
     *
     * @var string|null
     */
    private static $prefixCache = null;

    /**
     * Get WordPress table prefix (cached).
     *
     * @return string
     */
    public static function prefix(): string
    {
        if (self::$prefixCache === null) {
            global $wpdb;
            self::$prefixCache = $wpdb->prefix;
        }

        return self::$prefixCache;
    }

    /**
     * Get WordPress charset collate.
     *
     * @return string
     */
    public static function charsetCollate(): string
    {
        global $wpdb;
        return $wpdb->get_charset_collate();
    }

    /**
     * Get full table name for a table key.
     *
     * @param string $tableKey Table key (e.g., 'visitor', 'views').
     * @return string Full table name with prefix.
     */
    public static function table(string $tableKey): string
    {
        // Build cache on first access
        if (self::$tableCache === null) {
            self::buildTableCache();
        }

        return self::$tableCache[$tableKey] ?? self::generateTableName($tableKey);
    }

    /**
     * Get all table names.
     *
     * @param bool $includeAddons Include addon tables.
     * @return array<string, string> Table key => full name mapping.
     */
    public static function getAllTables(bool $includeAddons = true): array
    {
        if (self::$tableCache === null) {
            self::buildTableCache();
        }

        if ($includeAddons) {
            return self::$tableCache;
        }

        return array_filter(
            self::$tableCache,
            fn($key) => in_array($key, self::$coreTables, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Get only existing tables from database.
     *
     * @param array $except Tables to exclude.
     * @return array<string, string>
     */
    public static function getExistingTables(array $except = []): array
    {
        $allTables = self::getAllTables();
        $result    = [];

        foreach ($allTables as $key => $name) {
            if (in_array($key, $except, true)) {
                continue;
            }

            if (self::tableExists($name)) {
                $result[$key] = $name;
            }
        }

        return $result;
    }

    /**
     * Check if a table exists in the database.
     *
     * @param string $tableName Full table name.
     * @return bool
     */
    public static function tableExists(string $tableName): bool
    {
        global $wpdb;

        // Use cached query for better performance
        static $existingTables = null;

        if ($existingTables === null) {
            $existingTables = $wpdb->get_col('SHOW TABLES');
        }

        return in_array($tableName, $existingTables, true);
    }

    /**
     * Get table row count.
     *
     * @param string $tableKey Table key.
     * @return int
     */
    public static function getRowCount(string $tableKey): int
    {
        global $wpdb;

        $tableName = self::table($tableKey);

        if (!self::tableExists($tableName)) {
            return 0;
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$tableName}`");
    }

    /**
     * Get table information (status).
     *
     * @param string $tableKey Table key.
     * @return array|null
     */
    public static function getTableInfo(string $tableKey): ?array
    {
        global $wpdb;

        $tableName = self::table($tableKey);
        $result    = $wpdb->get_row("SHOW TABLE STATUS LIKE '{$tableName}'", ARRAY_A);

        return $result ?: null;
    }

    /**
     * Get human-readable table description.
     *
     * @param string $tableKey Table key.
     * @return string
     */
    public static function getTableDescription(string $tableKey): string
    {
        $descriptions = [
            'useronline'            => __('Tracks users currently online on your website.', 'wp-statistics'),
            'visitor'               => __('Records individual visitors and their activities.', 'wp-statistics'),
            'exclusions'            => __('Logs excluded views (bots, specific IPs, etc.).', 'wp-statistics'),
            'pages'                 => __('Stores page view counts.', 'wp-statistics'),
            'historical'            => __('Contains historical traffic data.', 'wp-statistics'),
            'visitor_relationships' => __('Links visitors to content interactions.', 'wp-statistics'),
            'views'                 => __('Raw view/hit data.', 'wp-statistics'),
            'events'                => __('Custom events (Data Plus add-on).', 'wp-statistics'),
            'ar_outbox'             => __('Report messages (Advanced Reporting add-on).', 'wp-statistics'),
            'campaigns'             => __('Marketing campaigns (Marketing add-on).', 'wp-statistics'),
            'goals'                 => __('Marketing goals (Marketing add-on).', 'wp-statistics'),
        ];

        return $descriptions[$tableKey] ?? '';
    }

    /**
     * Truncate a table.
     *
     * @param string $tableKey Table key.
     * @return bool Success.
     */
    public static function truncateTable(string $tableKey): bool
    {
        global $wpdb;

        $tableName = self::table($tableKey);

        if (!self::tableExists($tableName)) {
            return false;
        }

        $result = $wpdb->query("TRUNCATE TABLE `{$tableName}`");

        if ($result !== false) {
            /**
             * Fires after a table is truncated.
             *
             * @param string $tableKey Table key.
             */
            do_action('wp_statistics_truncate_table', $tableKey);
            return true;
        }

        return false;
    }

    /**
     * Optimize a table.
     *
     * @param string $tableKey Table key.
     * @return bool
     */
    public static function optimizeTable(string $tableKey): bool
    {
        global $wpdb;

        $tableName = self::table($tableKey);
        return $wpdb->query("OPTIMIZE TABLE `{$tableName}`") !== false;
    }

    /**
     * Repair a table.
     *
     * @param string $tableKey Table key.
     * @return bool
     */
    public static function repairTable(string $tableKey): bool
    {
        global $wpdb;

        $tableName = self::table($tableKey);
        return $wpdb->query("REPAIR TABLE `{$tableName}`") !== false;
    }

    /**
     * Get column information.
     *
     * @param string $tableKey Table key.
     * @param string $column   Column name.
     * @return object|null
     */
    public static function getColumn(string $tableKey, string $column): ?object
    {
        global $wpdb;

        $tableName = self::table($tableKey);
        $result    = $wpdb->get_row(
            $wpdb->prepare("SHOW COLUMNS FROM `{$tableName}` LIKE %s", $column)
        );

        return $result ?: null;
    }

    /**
     * Check if column has a specific type.
     *
     * @param string $tableKey Table key.
     * @param string $column   Column name.
     * @param string $type     Expected type.
     * @return bool
     */
    public static function isColumnType(string $tableKey, string $column, string $type): bool
    {
        $columnInfo = self::getColumn($tableKey, $column);

        if ($columnInfo && isset($columnInfo->Type)) {
            return strtolower($columnInfo->Type) === strtolower($type);
        }

        return false;
    }

    /**
     * Build the table name cache.
     *
     * @return void
     */
    private static function buildTableCache(): void
    {
        self::$tableCache = [];

        $allTables = array_merge(self::$coreTables, self::$addonTables);

        foreach ($allTables as $tableKey) {
            self::$tableCache[$tableKey] = self::generateTableName($tableKey);
        }
    }

    /**
     * Generate full table name from key.
     *
     * @param string $tableKey Table key.
     * @return string
     */
    private static function generateTableName(string $tableKey): string
    {
        return str_replace(
            ['[prefix]', '[name]'],
            [self::prefix(), $tableKey],
            self::TABLE_PATTERN
        );
    }

    /**
     * Clear the table cache (useful for multisite switching).
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$tableCache  = null;
        self::$prefixCache = null;
    }

    /**
     * Add INSERT IGNORE modifier to a query.
     *
     * @param string $query SQL query.
     * @return string Modified query.
     */
    public static function insertIgnore(string $query): string
    {
        return preg_replace('/^(INSERT INTO)/i', 'INSERT IGNORE INTO', $query, 1);
    }
}
