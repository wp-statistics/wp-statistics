<?php

namespace WP_Statistics\Service\Database;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Database\Migrations\Schema\SchemaMigration;
use WP_Statistics\Service\Database\Operations\AbstractTableOperation;
use WP_Statistics\Service\Database\Operations\Create;
use WP_Statistics\Service\Database\Operations\Drop;
use WP_Statistics\Service\Database\Operations\Insert;
use WP_Statistics\Service\Database\Operations\Inspect;
use WP_Statistics\Service\Database\Operations\InspectColumns;
use WP_Statistics\Service\Database\Operations\Repair;
use WP_Statistics\Service\Database\Operations\Select;
use WP_Statistics\Service\Database\Operations\Update;

/**
 * Factory for creating database operation and migration instances.
 *
 * This class provides methods to create specific operations (e.g., create, update, drop)
 * and manage different migration types (e.g., schema, data).
 */
class DatabaseFactory
{
    /**
     * Mapping of operation names to their corresponding classes.
     *
     * @var array
     */
    private static $operations = [
        'create'          => Create::class,
        'update'          => Update::class,
        'drop'            => Drop::class,
        'inspect'         => Inspect::class,
        'insert'          => Insert::class,
        'select'          => Select::class,
        'repair'          => Repair::class,
        'inspect_columns' => InspectColumns::class,
    ];

    /**
     * Mapping of migration types to their corresponding classes.
     *
     * @var array
     */
    private static $migrationTypes = [
        'schema' => SchemaMigration::class,
    ];

    /**
     * Create an instance of a specific table operation.
     *
     * @param string $operation The name of the operation (e.g., 'create', 'drop').
     * @return AbstractTableOperation An instance of the corresponding operation class.
     * @throws \InvalidArgumentException If the operation is invalid or the class does not exist.
     */
    public static function table($operation)
    {
        $operation = strtolower($operation);

        if (!isset(self::$operations[$operation])) {
            throw new \InvalidArgumentException("Invalid operation: {$operation}");
        }

        $providerClass = self::$operations[$operation];

        if (!class_exists($providerClass)) {
            throw new \InvalidArgumentException("Class not exist: {$providerClass}");
        }

        return new $providerClass();
    }

    /**
     * Create instances of all registered migration types.
     *
     * @return array An array of migration instances.
     */
    public static function migration()
    {
        $migrationInstances = [];

        foreach (self::$migrationTypes as $migrationClass) {
            if (!class_exists($migrationClass)) {
                continue;
            }

            $migrationInstances[] = new $migrationClass();
        }

        return $migrationInstances;
    }

    /**
     * Compare the current database version with a required version.
     *
     * This method retrieves the current version of the database from the 'db' option group
     * and compares it to a specified required version using a provided comparison operation.
     *
     * @param string $requiredVersion The version to compare against (e.g., "1.2.3").
     * @param string $operation The comparison operator for version comparison.
     *                                Allowed values: '<', '<=', '>', '>=', '==', '!='.
     *
     * @return bool Returns true if the comparison condition is met, false otherwise.
     *              Returns false if the current database version is not available.
     */
    public static function compareCurrentVersion($requiredVersion, $operation)
    {
        $version = Option::getOptionGroup('db', 'version', null);

        if (empty($version)) {
            return false;
        }

        return version_compare($version, $requiredVersion, $operation);
    }
}
