<?php

namespace WP_Statistics\Service\Admin\Database;

use WP_Statistics\Service\Admin\Database\Migrations\DataMigration;
use WP_Statistics\Service\Admin\Database\Migrations\SchemaMigration;
use WP_Statistics\Service\Admin\Database\Operations\Create;
use WP_Statistics\Service\Admin\Database\Operations\Drop;
use WP_Statistics\Service\Admin\Database\Operations\Insert;
use WP_Statistics\Service\Admin\Database\Operations\Inspect;
use WP_Statistics\Service\Admin\Database\Operations\Update;

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
        'create'  => Create::class,
        'update'  => Update::class,
        'drop'    => Drop::class,
        'inspect' => Inspect::class,
        'insert'  => Insert::class,
    ];

    /**
     * Mapping of migration types to their corresponding classes.
     * 
     * @var array
     */
    private static $migrationTypes = [
        'schema' => SchemaMigration::class,
        'data' => DataMigration::class,
    ];

    /**
     * Create an instance of a specific table operation.
     *
     * @param string $operation The name of the operation (e.g., 'create', 'drop').
     * @return object An instance of the corresponding operation class.
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
    public static function Migration()
    {
        $migrationInstances = [];

        foreach (self::$migrationTypes as $migrationClass) {
            if (! class_exists($migrationClass)) {
                continue;
            }

            $migrationInstances[] = new $migrationClass();
        }

        return $migrationInstances;
    }
}
