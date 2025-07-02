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
