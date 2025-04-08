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
        'parameters' => [
            'columns' => [
                'ID'          => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'session_id'  => 'bigint(20) UNSIGNED NOT NULL',
                'resource_id' => 'bigint(20) UNSIGNED NOT NULL',
                'view_id'     => 'bigint(20) UNSIGNED NOT NULL',
                'parameter'   => 'varchar(180)',
                'value'       => 'varchar(180)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY session_id (session_id)',
                'KEY resource_id (resource_id)',
                'KEY view_id (view_id)',
            ],
        ],
        'resources' => [
            'columns' => [
                'ID'                 => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'resource_type'      => 'varchar(50)',
                'resource_id'        => 'bigint(20) UNSIGNED NOT NULL',
                'resource_url'       => 'VARCHAR(255)',
                'cached_title'       => 'varchar(180)',
                'cached_terms'       => 'varchar(180)',
                'cached_author_id'   => 'bigint(20) UNSIGNED DEFAULT NULL',
                'cached_author_name' => 'varchar(180)',
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
        'views' => [
            'columns' => [
                'ID'           => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'session_id'   => 'bigint(20) UNSIGNED NOT NULL',
                'resource_id'  => 'bigint(20) UNSIGNED NOT NULL',
                'viewed_at'    => 'datetime',
                'next_view_id' => 'bigint(20) UNSIGNED DEFAULT NULL',
                'duration'     => 'bigint(20)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY session_id (session_id)',
                'KEY resource_id (resource_id)',
                'KEY viewed_at (viewed_at)',
            ],
        ],
        'countries' => [
            'columns' => [
                'ID'             => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'code'           => 'varchar(3) NOT NULL UNIQUE',
                'name'           => 'varchar(100) NOT NULL',
                'continent_code' => 'varchar(10) NOT NULL',
                'continent'      => 'varchar(50) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY continent_code (continent_code)',
            ],
        ],
        'cities' => [
            'columns' => [
                'ID'          => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'country_id'  => 'bigint(20) UNSIGNED NOT NULL',
                'region_code' => 'varchar(50) NOT NULL',
                'region_name' => 'varchar(100) NOT NULL',
                'city_name'   => 'varchar(100) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY country_id (country_id)',
                'KEY region_code (region_code)',
                'KEY city_name (city_name)',
            ],
        ],
        'device_types' => [
            'columns' => [
                'ID'   => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'name' => 'varchar(180)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'UNIQUE KEY name (name)',
            ],
        ],
        'device_browser_versions' => [
            'columns' => [
                'ID'         => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'browser_id' => 'bigint(20) UNSIGNED NOT NULL',
                'version'    => 'varchar(50) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'device_browsers' => [
            'columns' => [
                'ID'   => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'name' => 'varchar(180)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'device_oss' => [
            'columns' => [
                'ID'   => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'name' => 'varchar(180)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'resolutions' => [
            'columns' => [
                'ID'     => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'width'  => 'INT(5) NOT NULL',
                'height' => 'INT(5) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'languages' => [
            'columns' => [
                'ID'     => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'code'   => 'varchar(10) NOT NULL UNIQUE',
                'name'   => 'varchar(100)  NOT NULL',
                'region' => 'varchar(100)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY region (region)',
            ],
        ],
        'timezones' => [
            'columns' => [
                'ID'     => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'name'   => 'varchar(100) NOT NULL UNIQUE',
                'offset' => 'varchar(10) NOT NULL',
                'is_dst' => 'TINYINT(1) NOT NULL DEFAULT 0',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'referrers' => [
            'columns' => [
                'ID'      => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'channel' => 'varchar(100) NOT NULL',
                'name'    => 'varchar(100) NOT NULL',
                'domain'  => 'tinyint(1) NOT NULL DEFAULT 0',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'visitors' => [
            'columns' => [
                'ID'         => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'hash'       => 'varchar(180)',
                'created_at' => 'datetime',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'sessions' => [
            'columns' => [
                'ID'                        => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'visitor_id'                => 'bigint(20) UNSIGNED DEFAULT NULL',
                'ip'                        => 'varchar(60) NOT NULL',
                'referrer_id'               => 'bigint(20) UNSIGNED DEFAULT NULL',
                'country_id'                => 'bigint(20) UNSIGNED DEFAULT NULL',
                'city_id'                   => 'bigint(20) UNSIGNED DEFAULT NULL',
                'initial_view_id'           => 'bigint(20) UNSIGNED DEFAULT NULL',
                'last_view_id'              => 'bigint(20) UNSIGNED DEFAULT NULL',
                'total_views'               => 'int(11) NOT NULL DEFAULT 0',
                'device_type'               => 'bigint(20) UNSIGNED DEFAULT NULL',
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
            ],
        ],
        'reports' => [
            'columns' => [
                'ID'          => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'created_by'  => 'bigint(20) UNSIGNED DEFAULT NULL',
                'title'       => 'varchar(180)',
                'description' => 'varchar(180)',
                'filters'     => 'text',
                'widgets'     => 'text',
                'access_level'=> 'varchar(180)',
                'created_at'  => 'datetime',
                'updated_at'  => 'datetime',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'summary' => [
            'columns' => [
                'ID'             => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'date'           => 'datetime NOT NULL',
                'resource_id'    => 'bigint(20) UNSIGNED NOT NULL',
                'type'           => 'tinyint(3) UNSIGNED NOT NULL',
                'visitors'       => 'int(11) NOT NULL DEFAULT 0',
                'sessions'       => 'int(11) NOT NULL DEFAULT 0',
                'views'          => 'int(11) NOT NULL DEFAULT 0',
                'total_duration' => 'int(11) NOT NULL DEFAULT 0',
                'bounces'        => 'tinyint(3) UNSIGNED NOT NULL DEFAULT 0',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY date (date)',
                'KEY resource_id (resource_id)',
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
