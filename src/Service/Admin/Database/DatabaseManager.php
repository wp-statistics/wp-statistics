<?php
namespace WP_Statistics\Service\Admin\Database;

/**
 * Interface for database operations.
 *
 * Defines a contract for classes that perform database-related operations,
 * requiring an implementation of the `execute` method.
 */
interface DatabaseManager {
    /**
     * Execute the database operation.
     *
     * @return mixed The result of the operation, based on the implementation.
     */
    public function execute();
}