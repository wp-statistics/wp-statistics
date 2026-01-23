<?php

namespace WP_Statistics\Service\Database\Migrations;

use Exception;
use WP_Statistics\Service\Database\DatabaseFactory;

/**
 * Manages migrations related to database schema.
 */
class SchemaMigration extends AbstractMigrationOperation
{
    /**
     * The name of the migration operation.
     *
     * @var string
     */
    protected $name = 'schema';

    /**
     * The list of migration steps for this operation.
     *
     * This array maps version numbers to their corresponding migration methods.
     * Each version key represents a database schema migration that needs to be applied
     * for that specific version. The associated value is an array of method WP_Statistics_names
     * that should be executed for the migration step.
     *
     * Example:
     * 'x.x.x' => [
     *     'FirstMethodName',
     *     'SecondMethodName',
     * ],
     *
     * The method WP_Statistics_names specified should exist within this class, as this class
     * is specifically designed for handling schema migrations. Each method is
     * responsible for tasks such as altering table structures, adding or removing
     * columns, changing column types, or other schema-level changes to the database.
     *
     * Note: This class is not intended for data migrations such as transferring
     * data between tables, modifying data values, or other operations on the
     * contents of the database. Those operations should be handled by a dedicated
     * data migration class.
     *
     * @var array
     */
    protected $migrationSteps = [
        '14.12.6' => [
            'addFirstAndLastPageToVisitors',
        ],
        // '14.13.5' => [
        //     'dropDuplicateColumnsFromUserOnline'
        // ]
        '14.15'   => [
            'dropVisitTable',
            'addUserIdToEvents'
        ],
        '15.0.0'  => [
            'addResourceUriIdAndSessionIdToEvents',
            'addSessionIdEventNameIndexToEvents',
            'addResourceLookupIndex',
        ],
    ];

    /**
     * Adds 4 new columns to the 'visitors' table: 'first_page', 'first_view', 'last_page', and 'last_view'.
     *
     * @return void
     */
    public function addFirstAndLastPageToVisitors()
    {
        $this->ensureConnection();

        try {
            DatabaseFactory::table('update')
                ->setName('visitor')
                ->setArgs([
                    'add' => [
                        'first_page' => 'bigint(20) UNSIGNED DEFAULT NULL',
                        'first_view' => 'datetime DEFAULT NULL',
                        'last_page'  => 'bigint(20) UNSIGNED DEFAULT NULL',
                        'last_view'  => 'datetime DEFAULT NULL'
                    ]
                ])
                ->execute();
        } catch (Exception $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }

    public function dropVisitTable()
    {
        try {
            DatabaseFactory::table('drop')
                ->setName('visit')
                ->execute();
        } catch (Exception $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }

    /**
     * Removes duplicate columns from the 'user_online' table.
     *
     * @return void
     */
    public function dropDuplicateColumnsFromUserOnline()
    {
        try {
            DatabaseFactory::table('update')
                ->setName('useronline')
                ->setArgs([
                    'drop' => [
                        'referred',
                        'agent',
                        'platform',
                        'version',
                        'user_id',
                        'page_id',
                        'type',
                        'location',
                        'city',
                        'region',
                        'continent'
                    ]
                ])
                ->execute();
        } catch (Exception $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }

    /**
     * Adds `user_id` column to the 'events' table.
     *
     * @return void
     */
    public function addUserIdToEvents()
    {
        $this->ensureConnection();

        try {
            DatabaseFactory::table('update')
                ->setName('events')
                ->setArgs([
                    'add' => [
                        'user_id' => 'bigint(20) UNSIGNED DEFAULT NULL',
                    ]
                ])
                ->execute();
        } catch (Exception $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }

    /**
     * Updates the 'events' table to add new columns: 'resource_uri_id' and 'session_id'.
     *
     * @return void
     * @since 15.0.0
     */
    public function addResourceUriIdAndSessionIdToEvents()
    {
        $this->ensureConnection();

        try {
            DatabaseFactory::table('update')
                ->setName('events')
                ->setArgs([
                    'add' => [
                        'resource_uri_id' => 'BIGINT(20) UNSIGNED DEFAULT NULL',
                        'session_id'      => 'BIGINT(20) UNSIGNED DEFAULT NULL',
                    ]
                ])
                ->execute();
        } catch (Exception $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }

    /**
     * Adds composite index on (session_id, event_name) to the 'events' table for fast lookups (e.g., conversions).
     *
     * @return void
     * @since 15.0.0
     */
    public function addSessionIdEventNameIndexToEvents()
    {
        $this->ensureConnection();

        try {
            DatabaseFactory::table('repair')
                ->setName('events')
                ->setArgs([
                    'indexDefinition' => 'KEY `session_id_event_name` (`session_id`,`event_name`)',
                ])
                ->execute();
        } catch (\Throwable $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }

    /**
     * Adds composite index on (resource_id, resource_type, is_deleted) to the 'resources' table
     * for fast lookups when filtering by WordPress post ID and type.
     *
     * @return void
     * @since 15.0.0
     */
    public function addResourceLookupIndex()
    {
        $this->ensureConnection();

        try {
            DatabaseFactory::table('repair')
                ->setName('resources')
                ->setArgs([
                    'indexDefinition' => 'KEY `idx_resource_lookup` (`resource_id`,`resource_type`,`is_deleted`)',
                ])
                ->execute();
        } catch (\Throwable $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }
}
