<?php

namespace WP_Statistics\Service\Admin\Database\Migrations;

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
    protected $migrationSteps = [];
}
