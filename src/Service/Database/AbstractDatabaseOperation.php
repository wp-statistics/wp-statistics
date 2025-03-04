<?php

namespace WP_Statistics\Service\Database;

use WP_Statistics\Service\Database\Managers\TransactionHandler;

/**
 * Base class for database operations.
 *
 * This abstract class provides common functionality for database operations,
 * such as handling connections, validating table names, and managing arguments.
 */
abstract class AbstractDatabaseOperation implements DatabaseManager
{
    /**
     * WordPress database connection.
     *
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * Table name.
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * Full table name including prefix.
     *
     * @var string
     */
    protected $fullName = '';

    /**
     * Arguments for the operation.
     *
     * @var array
     */
    protected $args = [];

    /**
     * Handles database transactions.
     *
     * @var TransactionHandler
     */
    protected $transactionHandler;

    /**
     * Constructor to initialize dependencies.
     */
    public function __construct()
    {
        $this->ensureConnection();
    }

    /**
     * Set the operation arguments.
     *
     * @param array $args
     * @return $this
     */
    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Validate the table name.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateTableName()
    {
        if (empty($this->tableName)) {
            throw new \InvalidArgumentException('Table name is required');
        }
    }

    /**
     * Validate the operation arguments.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateArgs()
    {
        if (empty($this->args)) {
            throw new \InvalidArgumentException('Column definitions are required');
        }
    }

    /**
     * Generate the full table name with prefix.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setFullTableName()
    {
        if (empty($this->tableName)) {
            throw new \InvalidArgumentException('Table name must be set before proceeding.');
        }

        $this->fullName = $this->wpdb->prefix . 'statistics_' . $this->tableName;
    }

    /**
     * Ensure the database connection and initialize the transaction handler.
     *
     * @return void
     */
    protected function ensureConnection()
    {
        global $wpdb;

        $this->wpdb = $wpdb;

        $this->transactionHandler = new TransactionHandler($this->wpdb);
    }
}
