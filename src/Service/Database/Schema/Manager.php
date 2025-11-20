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
        'pages'                 => [
            'columns'     => [
                'page_id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
                'uri'     => 'varchar(190) NOT NULL',
                'type'    => 'varchar(180) NOT NULL',
                'date'    => 'date NOT NULL',
                'count'   => 'int(11) NOT NULL',
                'id'      => 'int(11) NOT NULL',
            ],
            'constraints' => [
                'page_id'      => 'PRIMARY KEY (page_id)',
                'date_uri'     => 'UNIQUE KEY date_2 (date, uri)',
                'uri'          => 'KEY url (uri)',
                'date'         => 'KEY date (date)',
                'id'           => 'KEY id (id)',
                'uri_count_id' => 'KEY uri (uri, count, id)',
            ],
        ],
        'historical'            => [
            'columns'     => [
                'ID'       => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'category' => 'varchar(25) NOT NULL',
                'page_id'  => 'bigint(20) NOT NULL',
                'uri'      => 'varchar(190) NOT NULL',
                'value'    => 'bigint(20) NOT NULL',
            ],
            'constraints' => [
                'ID'       => 'PRIMARY KEY (ID)',
                'category' => 'KEY category (category)',
                'uri'      => 'UNIQUE KEY uri (uri)',
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
                'ID'            => 'PRIMARY KEY (ID)',
                'date_ip_agent' => 'UNIQUE KEY date_ip_agent (last_counter, ip, agent(50), platform(50), version(50))',
                'agent'         => 'KEY agent (agent)',
                'platform'      => 'KEY platform (platform)',
                'version'       => 'KEY version (version)',
                'device'        => 'KEY device (device)',
                'model'         => 'KEY model (model)',
                'location'      => 'KEY location (location)',
                'ip'            => 'KEY ip (ip)',
            ],
        ],
        'exclusions'            => [
            'columns'     => [
                'ID'     => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'date'   => 'date NOT NULL',
                'reason' => 'varchar(180) DEFAULT NULL',
                'count'  => 'bigint(20) NOT NULL',
            ],
            'constraints' => [
                'ID'     => 'PRIMARY KEY (ID)',
                'date'   => 'KEY date (date)',
                'reason' => 'KEY reason (reason)',
            ],
        ],
        'events'                => [
            'columns'     => [
                'ID'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'date'       => 'datetime NOT NULL',
                'page_id'    => 'bigint(20) NULL',
                'visitor_id' => 'bigint(20) NULL',
                'user_id'    => 'bigint(20) UNSIGNED DEFAULT NULL',
                'event_name' => 'varchar(64) NOT NULL',
                'event_data' => 'text NOT NULL',
            ],
            'constraints' => [
                'ID'         => 'PRIMARY KEY (ID)',
                'visitor_id' => 'KEY visitor_id (visitor_id)',
                'page_id'    => 'KEY page_id (page_id)',
                'event_name' => 'KEY event_name (event_name)',
            ],
        ],
        'visitor_relationships' => [
            'columns'     => [
                'ID'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
                'visitor_id' => 'bigint(20) NOT NULL',
                'page_id'    => 'bigint(20) NOT NULL',
                'date'       => 'datetime NOT NULL',
            ],
            'constraints' => [
                'ID'         => 'PRIMARY KEY (ID)',
                'visitor_id' => 'KEY visitor_id (visitor_id)',
                'page_id'    => 'KEY page_id (page_id)',
            ],
        ],
        'summary_totals'        => [
            'columns'     => [
                'ID'         => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
                'date'       => 'date NOT NULL UNIQUE',
                'visitors'   => 'bigint(20) UNSIGNED NOT NULL',
                'views'      => 'bigint(20) UNSIGNED NOT NULL'
            ],
            'constraints' => [
                'PRIMARY KEY (ID)',
            ]
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
