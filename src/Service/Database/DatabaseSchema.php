<?php

namespace WP_Statistics\Service\Database;

/**
 * Database Schema Registry for WP Statistics v15.
 *
 * Provides table name management with caching for optimal performance.
 * Replaces legacy WP_STATISTICS\DB class.
 *
 * For the canonical legacy/v15/add-on table definitions and compatibility policy,
 * see: `docs/COMPATIBILITY.md`
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
     * Addon tables grouped by addon slug.
     *
     * @var array<string, array{name: string, tables: string[]}>
     */
    private static $addonTables = [
        'data-plus' => [
            'name'   => 'Data Plus',
            'tables' => ['events'],
        ],
        'advanced-reporting' => [
            'name'   => 'Advanced Reporting',
            'tables' => ['ar_outbox'],
        ],
        'marketing' => [
            'name'   => 'Marketing',
            'tables' => ['campaigns', 'goals'],
        ],
    ];

    /**
     * Legacy tables (v14 schema, deprecated in v15).
     *
     * @var array
     */
    private static $legacyTables = [
        'useronline',            // Replaced by real-time online visitors system
        'visitor',               // Replaced by 'visitors' (normalized v15 schema)
        'pages',                 // Replaced by 'views' table
        'historical',            // Replaced by 'summary' and 'summary_totals'
        'visitor_relationships', // Legacy visitor-content linking
    ];

    /**
     * Cached table WP_Statistics_names.
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
     * Get all table WP_Statistics_names.
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
     * Check if a table is a legacy table (deprecated in v15).
     *
     * @param string $tableKey Table key.
     * @return bool
     */
    public static function isLegacyTable(string $tableKey): bool
    {
        return in_array($tableKey, self::$legacyTables, true);
    }

    /**
     * Check if a table is an add-on table.
     *
     * @param string $tableKey Table key.
     * @return bool
     */
    public static function isAddonTable(string $tableKey): bool
    {
        foreach (self::$addonTables as $addon) {
            if (in_array($tableKey, $addon['tables'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the add-on name for a table.
     *
     * @param string $tableKey Table key.
     * @return string|null Add-on name or null if not an add-on table.
     */
    public static function getAddonName(string $tableKey): ?string
    {
        foreach (self::$addonTables as $addon) {
            if (in_array($tableKey, $addon['tables'], true)) {
                return $addon['name'];
            }
        }

        return null;
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
            // Legacy tables
            'useronline'              => __('Real-time online tracking.', 'wp-statistics'),
            'visitor'                 => __('Old visitor records.', 'wp-statistics'),
            'pages'                   => __('Page view counts.', 'wp-statistics'),
            'historical'              => __('Historical traffic data.', 'wp-statistics'),
            'visitor_relationships'   => __('Visitor-content links.', 'wp-statistics'),
            // Core tables
            'exclusions'              => __('Logs excluded views (bots, specific IPs, etc.).', 'wp-statistics'),
            'resources'               => __('Content metadata (posts, pages, custom types).', 'wp-statistics'),
            'resource_uris'           => __('URI paths for resources.', 'wp-statistics'),
            'parameters'              => __('URL query parameters.', 'wp-statistics'),
            'views'                   => __('Individual page view records.', 'wp-statistics'),
            'visitors'                => __('Unique visitor records with fingerprints.', 'wp-statistics'),
            'sessions'                => __('Visitor sessions with device and location.', 'wp-statistics'),
            'countries'               => __('Country lookup table.', 'wp-statistics'),
            'cities'                  => __('City lookup table.', 'wp-statistics'),
            'device_types'            => __('Device type lookup (desktop, mobile, tablet).', 'wp-statistics'),
            'device_browsers'         => __('Browser name lookup table.', 'wp-statistics'),
            'device_browser_versions' => __('Browser version lookup table.', 'wp-statistics'),
            'device_oss'              => __('Operating system lookup table.', 'wp-statistics'),
            'resolutions'             => __('Screen resolution lookup table.', 'wp-statistics'),
            'languages'               => __('Language preference lookup table.', 'wp-statistics'),
            'timezones'               => __('Timezone lookup table.', 'wp-statistics'),
            'referrers'               => __('Referrer URL lookup table.', 'wp-statistics'),
            'reports'                 => __('Generated analytics reports.', 'wp-statistics'),
            'summary'                 => __('Daily metrics summary per resource.', 'wp-statistics'),
            'summary_totals'          => __('Site-wide daily summary totals.', 'wp-statistics'),
            // Addon tables
            'events'                  => __('Custom events tracking.', 'wp-statistics'),
            'ar_outbox'               => __('Scheduled report queue.', 'wp-statistics'),
            'campaigns'               => __('Marketing campaigns.', 'wp-statistics'),
            'goals'                   => __('Marketing goals.', 'wp-statistics'),
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
     * Get all addon table keys as a flat array.
     *
     * @return string[]
     */
    private static function getAddonTableKeys(): array
    {
        $keys = [];
        foreach (self::$addonTables as $addon) {
            $keys = array_merge($keys, $addon['tables']);
        }
        return $keys;
    }

    /**
     * Build the table name cache.
     *
     * @return void
     */
    private static function buildTableCache(): void
    {
        self::$tableCache = [];

        $allTables = array_merge(self::$coreTables, self::getAddonTableKeys());

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
