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
     * @var array
     */
    protected $migrationSteps = [];
}
