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
        'useronline' => [
            'columns' => [
                'ID'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'ip'         => 'varchar(60) NOT NULL',
                'created'    => 'int(11)',
                'timestamp'  => 'int(10) NOT NULL',
                'date'       => 'datetime NOT NULL',
                'referred'   => 'text CHARACTER SET utf8 NOT NULL',
                'agent'      => 'varchar(255) NOT NULL',
                'platform'   => 'varchar(255)',
                'version'    => 'varchar(255)',
                'location'   => 'varchar(10)',
                'city'       => 'varchar(100)',
                'region'     => 'varchar(100)',
                'continent'  => 'varchar(50)',
                'visitor_id' => 'bigint(20) NOT NULL',
                'user_id'    => 'BIGINT(48) NOT NULL',
                'page_id'    => 'BIGINT(48) NOT NULL',
                'type'       => 'VARCHAR(100) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY ip (ip)'
            ],
        ],
        'pages' => [
            'columns' => [
                'page_id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
                'uri'     => 'varchar(190) NOT NULL',
                'type'    => 'varchar(180) NOT NULL',
                'date'    => 'date NOT NULL',
                'count'   => 'int(11) NOT NULL',
                'id'      => 'int(11) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (page_id)',
                'UNIQUE KEY date_2 (date, uri)',
                'KEY url (uri)',
                'KEY date (date)',
                'KEY id (id)',
                'KEY uri (uri, count, id)',
            ],
        ],
        'historical' => [
            'columns' => [
                'ID'       => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'category' => 'varchar(25) NOT NULL',
                'page_id'  => 'bigint(20) NOT NULL',
                'uri'      => 'varchar(190) NOT NULL',
                'value'    => 'bigint(20) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY category (category)',
                'UNIQUE KEY uri (uri)',
            ],
        ],
        'visit' => [
            'columns' => [
                'ID'           => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'last_visit'   => 'datetime NOT NULL',
                'last_counter' => 'date NOT NULL',
                'visit'        => 'int(10) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'UNIQUE KEY unique_date (last_counter)',
            ],
        ],
        'visitor' => [
            'columns' => [
                'ID'             => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'last_counter'   => 'date NOT NULL',
                'referred'       => 'text NOT NULL',
                'agent'          => 'varchar(180) NOT NULL',
                'platform'       => 'varchar(180)',
                'version'        => 'varchar(180)',
                'device'         => 'varchar(180)',
                'model'          => 'varchar(180)',
                'UAString'       => 'varchar(190)',
                'ip'             => 'varchar(60) NOT NULL',
                'location'       => 'varchar(10)',
                'user_id'        => 'BIGINT(40) NOT NULL',
                'hits'           => 'int(11)',
                'honeypot'       => 'int(11)',
                'city'           => 'varchar(100)',
                'region'         => 'varchar(100)',
                'continent'      => 'varchar(50)',
                'source_channel' => 'varchar(50)',
                'source_name'    => 'varchar(100)',
                'first_page'     => 'bigint(20) UNSIGNED DEFAULT NULL',
                'first_view'     => 'datetime DEFAULT NULL',
                'last_page'      => 'bigint(20) UNSIGNED DEFAULT NULL',
                'last_view'      => 'datetime DEFAULT NULL'
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'UNIQUE KEY date_ip_agent (last_counter, ip, agent(50), platform(50), version(50))',
                'KEY agent (agent)',
                'KEY platform (platform)',
                'KEY version (version)',
                'KEY device (device)',
                'KEY model (model)',
                'KEY location (location)',
                'KEY ip (ip)',
            ],
        ],
        'exclusions' => [
            'columns' => [
                'ID'     => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'date'   => 'date NOT NULL',
                'reason' => 'varchar(180) DEFAULT NULL',
                'count'  => 'bigint(20) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY date (date)',
                'KEY reason (reason)',
            ],
        ],
        'events' => [
            'columns' => [
                'ID'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'date'       => 'datetime NOT NULL',
                'page_id'    => 'bigint(20) NULL',
                'visitor_id' => 'bigint(20) NULL',
                'event_name' => 'varchar(64) NOT NULL',
                'event_data' => 'text NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY visitor_id (visitor_id)',
                'KEY page_id (page_id)',
                'KEY event_name (event_name)',
            ],
        ],
        'visitor_relationships' => [
            'columns' => [
                'ID'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'visitor_id' => 'bigint(20) NOT NULL',
                'page_id'    => 'bigint(20) NOT NULL',
                'date'       => 'datetime NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY visitor_id (visitor_id)',
                'KEY page_id (page_id)',
            ],
        ],
        // new ones.
        'parameters' => [
            'columns' => [
                'ID'          => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'session_id'  => 'bigint(20) NOT NULL',
                'resource_id' => 'bigint(20) NOT NULL',
                'view_id'     => 'bigint(20) NOT NULL',
                'parameter'   => 'varchar(180)',  // size should be decide
                'value'       => 'varchar(180)',  // size should be decide
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
                'ID'                 => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'resource_type'      => 'varchar(50)',
                'resource_id'        => 'bigint(20) NOT NULL',
                'resource_url'       => 'VARCHAR(255)',
                'cached_title'       => 'varchar(180)',  // size should be decide
                'cached_terms'       => 'varchar(180)',  // size should be decide
                'cached_author_id'   => 'bigint(20)',  // size should be decide
                'cached_author_name' => 'varchar(180)',  // size should be decide
                'cached_date'        => 'datetime',  // size should be decide
                'resource_meta'      => 'text',  // size should be decide
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY resource_type (resource_type)',
                'KEY resource_id (resource_id)',
            ],
        ],
        'views' => [
            'columns' => [
                'ID'           => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'session_id'   => 'bigint(20) NOT NULL',
                'resource_id'  => 'bigint(20) NOT NULL',
                'viewed_at'    => 'datetime',
                'next_view_id' => 'bigint(20)',
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
                'ID'             => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'code'           => 'varchar(3) NOT NULL UNIQUE', // size should be decide
                'name'           => 'varchar(100) NOT NULL', // size should be decide
                'continent_code' => 'varchar(10) NOT NULL', // size should be decide
                'continent'      => 'varchar(50) NOT NULL', // size should be decide
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'KEY continent_code (continent_code)',
            ],
        ],
        'cities' => [
            'columns' => [
                'ID'          => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'country_id'  => 'bigint(20) NOT NULL',
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
                'ID'   => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'name' => 'varchar(180)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
                'UNIQUE KEY name (name)',
            ],
        ],
        'device_browser_versions' => [
            'columns' => [
                'ID'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'browser_id' => 'bigint(20) NOT NULL',
                'version'    => 'varchar(50) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'device_browsers' => [
            'columns' => [
                'ID'   => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'name' => 'varchar(180)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'device_oss' => [
            'columns' => [
                'ID'   => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'name' => 'varchar(180)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'resolutions' => [
            'columns' => [
                'ID'     => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'width'  => 'INT(5) NOT NULL',
                'height' => 'INT(5) NOT NULL',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'languages' => [
            'columns' => [
                'ID'     => 'bigint(20) NOT NULL AUTO_INCREMENT',
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
                'ID'     => 'bigint(20) NOT NULL AUTO_INCREMENT',
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
                'ID'      => 'bigint(20) NOT NULL AUTO_INCREMENT',
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
                'ID'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'hash'       => 'varchar(180)',
                'created_at' => 'datetime',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'sessions' => [
            'columns' => [
                'ID'                        => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'visitor_id'                => 'bigint(20)',
                'ip'                        => 'varchar(45) NOT NULL',
                'referrer_id'               => 'bigint(20)',
                'country_id'                => 'bigint(20)',
                'city_id'                   => 'bigint(20)',
                'initial_view_id'           => 'bigint(20)',
                'last_view_id'              => 'bigint(20)',
                'total_views'               => 'int(11) NOT NULL DEFAULT 0',
                'device_type'               => 'bigint(20)',
                'device_os_id'              => 'bigint(20)',
                'device_browser_id'         => 'bigint(20)',
                'device_browser_version_id' => 'bigint(20)',
                'started_at'                => 'datetime NOT NULL',
                'ended_at'                  => 'datetime',
                'duration'                  => 'int(11)',
                'user_id'                   => 'bigint(20)',
                'timezone_id'               => 'bigint(20)',
                'language_id'               => 'bigint(20)',
                'resolution_id'             => 'bigint(20)',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'reports' => [
            'columns' => [
                'ID'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'created_by' => 'bigint(20)',
                'title' => 'varchar(180)',
                'description' => 'varchar(180)',
                'filters' => 'text',
                'widgets' => 'text',
                'access_level' => 'varchar(180)',
                'created_at' => 'datetime',
                'updated_at' => 'datetime',
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ],
        ],
        'summary' => [
            'columns' => [
                'ID'             => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'date'           => 'datetime NOT NULL',
                'resource_id'    => 'bigint(20) NOT NULL',
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
