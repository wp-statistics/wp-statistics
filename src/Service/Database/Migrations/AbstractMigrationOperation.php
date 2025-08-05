<?php

namespace WP_Statistics\Service\Database\Migrations;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\AbstractDatabaseOperation;

/**
 * Abstract base class for database migration operations.
 *
 * Provides methods for managing migration versions, executing migration steps,
 * and handling metadata related to database migrations.
 */
abstract class AbstractMigrationOperation extends AbstractDatabaseOperation
{
    /**
     * Target database version for this migration.
     *
     * @var string
     */
    protected $targetVersion;

    /**
     * Current database version.
     *
     * @var string
     */
    protected $currentVersion;

    /**
     * Current migration method being executed.
     *
     * @var string
     */
    protected $method;

    /**
     * List of migration steps and their corresponding versions.
     *
     * @var array
     */
    protected $migrationSteps = [];

    /**
     * Name of the migration operation.
     *
     * @var string
     */
    protected $name;

    /**
     * Initializes the migration operation by retrieving the current database version.
     */
    public function __construct()
    {
        $this->currentVersion = Option::getOptionGroup('db', 'version', '0.0.0');
    }

    /**
     * Retrieves the name of the migration operation.
     *
     * @return string The name of the migration.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Updates the database version to the specified version.
     *
     * @return void
     */
    public function setVersion()
    {
        if (! $this->isPassed()) {
            return;
        }

        Option::saveOptionGroup('version', $this->targetVersion, 'db');
    }

    /**
     * Sets the current migration method and updates the database version.
     *
     * @param string $method The migration method to set.
     * @param string $version The version associated with the method.
     */
    public function setMethod($method, $version)
    {
        $this->method        = $method;
        $this->targetVersion = $version;
    }

    /**
     * Executes the currently set migration method.
     *
     * @return void
     */
    public function execute()
    {
        if (!method_exists($this, $this->method) || empty($this->targetVersion)) {
            return;
        }

        call_user_func([$this, $this->method]);

        $this->setVersion();
    }

    /**
     * Retrieves the list of migration steps.
     *
     * @return array The migration steps and their associated versions.
     */
    public function getMigrationSteps()
    {
        return $this->migrationSteps;
    }

    /**
     * Sets the migration status to "failed" with an error message.
     *
     * @param string $message The error message describing why the migration failed.
     * @return void
     */
    protected function setErrorStatus($message)
    {
        Option::saveOptionGroup('migration_status_detail', [
            'status' => 'failed',
            'message' => $message
        ], 'db');
    }

    /**
     * Checks whether the migration process is considered to have passed.
     *
     * @return bool|null
     */
    public function isPassed() {
        $details = Option::getOptionGroup('db', 'migration_status_detail', null);

        if (empty($details['status'])) {
            return true;
        }

        if ($details['status'] === 'failed') {
            return;
        }

        return true;
    }
}
