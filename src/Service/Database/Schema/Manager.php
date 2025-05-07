<?php

namespace WP_Statistics\Service\Database\Schema;

/**
 * Manages database table schemas.
 *
 * This class provides methods to retrieve schemas and manage table names
 * for database operations.
 */
class Manager
{
    /**
     * The schema definitions for database tables.
     *
     * @var array
     */
    private static $tablesSchema = [
        'parameters'              => [
            'columns'     => [
                'ID'          => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'session_id'  => 'bigint(20) UNSIGNED NOT NULL',
                'resource_id' => 'bigint(20) UNSIGNED NOT NULL',
                'view_id'     => 'bigint(20) UNSIGNED NOT NULL',
                'parameter'   => 'varchar(64)',
                'value'       => 'varchar(255)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY session_id (session_id)',
                'KEY resource_id (resource_id)',
                'KEY view_id (view_id)',
            ],
        ],
        'resources'               => [
            'columns'     => [
                'ID'                 => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'resource_type'      => 'varchar(50)',
                'resource_id'        => 'bigint(20) UNSIGNED NOT NULL',
                'resource_url'       => 'VARCHAR(255)',
                'cached_title'       => 'text',
                'cached_terms'       => 'text',
                'cached_author_id'   => 'bigint(20) UNSIGNED DEFAULT NULL',
                'cached_author_name' => 'varchar(250)',
                'cached_date'        => 'datetime',
                'resource_meta'      => 'text',
                'is_deleted'         => 'tinyint(1) NOT NULL DEFAULT 0',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY resource_type (resource_type)',
                'KEY resource_id (resource_id)',
            ],
        ],
        'views'                   => [
            'columns'     => [
                'ID'           => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'session_id'   => 'bigint(20) UNSIGNED NOT NULL',
                'resource_id'  => 'bigint(20) UNSIGNED NOT NULL',
                'viewed_at'    => 'datetime',
                'next_view_id' => 'bigint(20) UNSIGNED DEFAULT NULL',
                'duration'     => 'bigint(11) UNSIGNED DEFAULT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY session_id (session_id)',
                'KEY resource_id (resource_id)',
            ],
        ],
        'countries'               => [
            'columns'     => [
                'ID'             => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'code'           => 'varchar(4) NOT NULL UNIQUE',
                'name'           => 'varchar(64) NOT NULL',
                'continent_code' => 'varchar(4) NOT NULL',
                'continent'      => 'varchar(16) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY continent_code (continent_code)',
            ],
        ],
        'cities'                  => [
            'columns'     => [
                'ID'          => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'country_id'  => 'bigint(20) UNSIGNED NOT NULL',
                'region_code' => 'varchar(4)',
                'region_name' => 'varchar(64) NOT NULL',
                'city_name'   => 'varchar(64) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY country_id (country_id)',
                'KEY region_code (region_code)',
                'KEY city_name (city_name)',
            ],
        ],
        'device_types'            => [
            'columns'     => [
                'ID'   => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'name' => 'varchar(64) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'UNIQUE KEY name (name)',
            ],
        ],
        'device_browser_versions' => [
            'columns'     => [
                'ID'         => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'browser_id' => 'bigint(20) UNSIGNED NOT NULL',
                'version'    => 'varchar(64) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY version (version)',
            ],
        ],
        'device_browsers'         => [
            'columns'     => [
                'ID'   => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'name' => 'varchar(64)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY name (name)',
            ],
        ],
        'device_oss'              => [
            'columns'     => [
                'ID'   => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'name' => 'varchar(64)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY name (name)',
            ],
        ],
        'resolutions'             => [
            'columns'     => [
                'ID'     => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'width'  => 'INT(5) NOT NULL',
                'height' => 'INT(5) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'languages'               => [
            'columns'     => [
                'ID'     => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'code'   => 'varchar(8) NOT NULL',
                'name'   => 'varchar(64) NOT NULL',
                'region' => 'varchar(4)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY name (name)',
            ],
        ],
        'timezones'               => [
            'columns'     => [
                'ID'     => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'name'   => 'varchar(128) NOT NULL UNIQUE',
                'offset' => 'varchar(16) NOT NULL',
                'is_dst' => 'TINYINT(1) NOT NULL DEFAULT 0',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'referrers'               => [
            'columns'     => [
                'ID'      => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'channel' => 'varchar(128)',
                'name'    => 'varchar(128) NOT NULL',
                'domain'  => 'varchar(128) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY domain (domain)',
                'KEY name (name)',
            ],
        ],
        'visitors'                => [
            'columns'     => [
                'ID'         => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'hash'       => 'varchar(128)',
                'created_at' => 'datetime',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY hash (hash)',
            ],
        ],
        'sessions'                => [
            'columns'     => [
                'ID'                        => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'visitor_id'                => 'bigint(20) UNSIGNED DEFAULT NULL',
                'ip'                        => 'varchar(60) DEFAULT NULL',
                'referrer_id'               => 'bigint(20) UNSIGNED DEFAULT NULL',
                'country_id'                => 'bigint(20) UNSIGNED DEFAULT NULL',
                'city_id'                   => 'bigint(20) UNSIGNED DEFAULT NULL',
                'initial_view_id'           => 'bigint(20) UNSIGNED DEFAULT NULL',
                'last_view_id'              => 'bigint(20) UNSIGNED DEFAULT NULL',
                'total_views'               => 'int(11) NOT NULL DEFAULT 1',
                'device_type_id'            => 'bigint(20) UNSIGNED DEFAULT NULL',
                'device_os_id'              => 'bigint(20) UNSIGNED DEFAULT NULL',
                'device_browser_id'         => 'bigint(20) UNSIGNED DEFAULT NULL',
                'device_browser_version_id' => 'bigint(20) UNSIGNED DEFAULT NULL',
                'started_at'                => 'datetime NOT NULL',
                'ended_at'                  => 'datetime',
                'duration'                  => 'int(11)',
                'user_id'                   => 'bigint(20) UNSIGNED DEFAULT NULL',
                'timezone_id'               => 'bigint(20) UNSIGNED DEFAULT NULL',
                'language_id'               => 'bigint(20) UNSIGNED DEFAULT NULL',
                'resolution_id'             => 'bigint(20) UNSIGNED DEFAULT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY visitor_id (visitor_id)',
                'KEY country_id (country_id)',
                'KEY referrer_id (referrer_id)',
                'KEY city_id (city_id)',
                'KEY initial_view_id (initial_view_id)',
                'KEY last_view_id (last_view_id)',
                'KEY device_type_id (device_type_id)',
                'KEY device_os_id (device_os_id)',
                'KEY device_browser_id (device_browser_id)',
                'KEY device_browser_version_id (device_browser_version_id)',
                'KEY timezone_id (timezone_id)',
                'KEY language_id (language_id)',
                'KEY resolution_id (resolution_id)',
            ],
        ],
        'reports'                 => [
            'columns'     => [
                'ID'           => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'created_by'   => 'bigint(20) UNSIGNED DEFAULT NULL',
                'title'        => 'varchar(128)',
                'description'  => 'varchar(256)',
                'filters'      => 'text',
                'widgets'      => 'text',
                'access_level' => 'varchar(128)',
                'created_at'   => 'datetime NOT NULL',
                'updated_at'   => 'datetime',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'summary'                 => [
            'columns'     => [
                'ID'             => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'date'           => 'datetime NOT NULL',
                'resource_id'    => 'bigint(20) UNSIGNED NOT NULL',
                'visitors'       => 'bigint(20) UNSIGNED NOT NULL',
                'sessions'       => 'bigint(20) UNSIGNED NOT NULL',
                'views'          => 'bigint(20) UNSIGNED NOT NULL',
                'total_duration' => 'int(11) NOT NULL DEFAULT 0',
                'bounces'        => 'tinyint(4) UNSIGNED NOT NULL DEFAULT 0',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY resource_id (resource_id)',
            ],
        ],
        'summary_totals'     => [
            'columns'     => [
                'ID'             => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'date'           => 'datetime NOT NULL',
                'visitors'       => 'bigint(20) UNSIGNED NOT NULL',
                'sessions'       => 'bigint(20) UNSIGNED NOT NULL',
                'views'          => 'bigint(20) UNSIGNED NOT NULL',
                'total_duration' => 'int(11) NOT NULL DEFAULT 0',
                'bounces'        => 'tinyint(4) UNSIGNED NOT NULL DEFAULT 0',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY date (date)',
            ],
        ],
    ];

    /**
     * Retrieve the fully defined schema (columns and constraints) for a specific table.
     *
     * @param string $tableName The name of the table.
     * @return array|null The schema for the table or null if not found.
     */
    public static function getSchemaForTable(string $tableName)
    {
        return self::$tablesSchema[$tableName] ?? null;
    }

    /**
     * Retrieve all table names.
     *
     * @return array An array of all table names defined in the schema.
     */
    public static function getAllTableNames()
    {
        return array_keys(self::$tablesSchema);
    }
}
