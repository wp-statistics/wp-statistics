<?php

namespace WP_Statistics\Service\Admin\Database\Managers;

class TransactionHandler
{
    /**
     * WordPress database connection instance.
     * 
     * @var \wpdb
     */
    private $wpdb;

    /**
     * Flag to track if a transaction is currently active
     * @var bool
     */
    private $transactionActive = false;

    /**
     * Constructor to inject the WordPress database instance.
     *
     * @param \wpdb $wpdb
     */
    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    /**
     * Start a new database transaction.
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function beginTransaction()
    {
        if ($this->transactionActive) {
            throw new \RuntimeException('Transaction already in progress');
        }

        $this->wpdb->query('SET autocommit = 0');

        if ($this->wpdb->query('START TRANSACTION') === false) {
            throw new \RuntimeException($this->wpdb->last_error ?: 'Failed to start transaction');
        }

        $this->transactionActive = true;
        return true;
    }

    /**
     * Commit the current transaction.
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function commitTransaction()
    {
        if (!$this->transactionActive) {
            throw new \RuntimeException('No active transaction to commit');
        }

        if ($this->wpdb->query('COMMIT') === false) {
            throw new \RuntimeException($this->wpdb->last_error ?: 'Failed to commit transaction');
        }

        $this->wpdb->query('SET autocommit = 1');
        $this->transactionActive = false;
        return true;
    }

    /**
     * Rollback the current transaction.
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function rollbackTransaction()
    {
        if (!$this->transactionActive) {
            throw new \RuntimeException('No active transaction to rollback');
        }

        if ($this->wpdb->query('ROLLBACK') === false) {
            throw new \RuntimeException($this->wpdb->last_error ?: 'Failed to rollback transaction');
        }

        $this->wpdb->query('SET autocommit = 1');
        $this->transactionActive = false;
        return true;
    }

    /**
     * Execute a callback within a transaction.
     *
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    public function executeInTransaction(callable $callback)
    {
        try {
            $this->beginTransaction();
            $result = $callback();
            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            if ($this->transactionActive) {
                $this->rollbackTransaction();
            }
            throw $e;
        }
    }
}
