<?php

namespace WP_Statistics\Service\Admin\Database\Migrations;

use Exception;
use WP_Statistics\Service\Admin\Database\DatabaseFactory;

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
     * for that specific version. The associated value is an array of method names
     * that should be executed for the migration step.
     * 
     * Example:
     * 'x.x.x' => [
     *     'FirstMethodName',
     *     'SecondMethodName',
     * ],
     * 
     * The method names specified should exist within this class, as this class
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
        '14.13' => [
            'addResourceTypeToEvent',
            'renamePageIdToResourceIdInHistorical',
            'renameEventNameToEventTypeInEvents',
            'updatePagesTableStructure'
        ],
    ];

    /**
     * Adds a new 'resource_type' column to the 'events' table.
     * 
     * @return void
     */
    public function addResourceTypeToEvent()
    {
        try {
            DatabaseFactory::table('update')
                ->setName('events')
                ->setArgs([
                    'add' => [
                        'resource_type' => 'varchar(100) NOT NULL',
                    ],
                ])
                ->execute();
        } catch (Exception $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }

    /**
     * Renames the 'page_id' column to 'resource_id' in the 'historical' table.
     * 
     * @return void
     */
    public function renamePageIdToResourceIdInHistorical()
    {
        try {
            DatabaseFactory::table('update')
                ->setName('historical')
                ->setArgs([
                    'rename' => [
                        'page_id' => [
                            'new_name' => 'resource_id',
                            'definition' => 'bigint(20) NOT NULL',
                        ],
                    ],
                ])
                ->execute();
        } catch (Exception $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }

    /**
     * Renames the 'event_name' column to 'event_type' in the 'events' table.
     * 
     * @return void
     */
    public function renameEventNameToEventTypeInEvents()
    {
        try {
            DatabaseFactory::table('update')
                ->setName('events')
                ->setArgs([
                    'rename' => [
                        'page_id' => [
                            'new_name' => 'resource_id',
                            'definition' => 'bigint(20) NULL',
                        ],
                    ],
                ])
                ->execute();
        } catch (Exception $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }

    /**
     * Updates the structure of the 'pages' table by:
     * - Adding a 'resource_id' column.
     * - Removing 'page_id' and 'type' columns.
     * 
     * @return void
     */
    public function updatePagesTableStructure()
    {
        try {
            DatabaseFactory::table('update')
                ->setName('pages')
                ->setArgs([
                    'add' => [
                        'resource_id' => 'bigint(20) NOT NULL',
                    ],
                    'drop' => [
                        'page_id',
                        'type',
                    ],
                ])
                ->execute();
        } catch (Exception $e) {
            $this->setErrorStatus($e->getMessage());
        }
    }
}
