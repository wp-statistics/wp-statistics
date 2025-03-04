<?php

namespace WP_Statistics\Service\Database\Operations;

use Exception;
use RuntimeException;

/**
 * Handles custom SELECT queries dynamically on database tables.
 *
 * Allows executing parameterized SELECT queries using structured arguments.
 */
class Select extends AbstractTableOperation
{
    /**
     * Stores the result of the executed query.
     *
     * @var array|null
     */
    private $result;

    /**
     * Output format (ARRAY_A, ARRAY_N, OBJECT).
     *
     * @var string
     */
    private $outputFormat = ARRAY_A;

    /**
     * Sets the output format (ARRAY_A, ARRAY_N, OBJECT).
     *
     * @param string $format
     * @return $this
     */
    public function setOutputFormat($format)
    {
        $this->outputFormat = $format;
        return $this;
    }

    /**
     * Executes the SELECT query based on arguments.
     *
     * @return $this
     * @throws RuntimeException if the query fails.
     */
    public function execute()
    {
        try {
            $this->ensureConnection();
            $this->validateTableName();
            $this->setFullTableName();

            if (empty($this->args['columns'])) {
                throw new RuntimeException("No columns specified for SELECT query.");
            }

            // Build SELECT statement dynamically
            $columns = implode(', ', $this->args['columns']);
            $sql = "SELECT {$columns} FROM {$this->fullName}";

            // Add WHERE clause if provided
            $params       = [];
            $whereClauses = [];

            if (!empty($this->args['where'])) {
                foreach ($this->args['where'] as $column => $value) {
                    $whereClauses[] = "`$column` = %s";
                    $params[] = $value;
                }
            }

            // Add WHERE IN clause if provided
            if (!empty($this->args['where_in'])) {
                foreach ($this->args['where_in'] as $column => $values) {
                    if (!is_array($values) || empty($values)) {
                        throw new RuntimeException("Invalid value for WHERE IN clause.");
                    }

                    $placeholders = implode(',', array_fill(0, count($values), '%s'));
                    $whereClauses[] = "`$column` IN ($placeholders)";
                    foreach ($values as $value) {
                        $params[] = $value;
                    }
                }
            }

            // Add WHERE conditions to SQL query
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }

            // Add GROUP BY clause if provided
            if (!empty($this->args['group_by'])) {
                $sql .= " GROUP BY {$this->args['group_by']}";
            }

            // Add ORDER BY clause if provided
            if (!empty($this->args['order_by'])) {
                $sql .= " ORDER BY {$this->args['order_by']}";
            }

            // Add LIMIT clause if provided
            if (!empty($this->args['limit']) && is_array($this->args['limit']) && count($this->args['limit']) === 2) {
                $sql .= " LIMIT %d OFFSET %d";
                $params[] = $this->args['limit'][0];
                $params[] = $this->args['limit'][1];
            }

            // Prepare the query with parameters
            if (!empty($params)) {
                array_unshift($params, $sql);
                $preparedQuery = call_user_func_array([$this->wpdb, 'prepare'], $params);
            } else {
                $preparedQuery = $sql;
            }

            // Execute the query
            $this->result = $this->wpdb->get_results($preparedQuery, $this->outputFormat);

            if ($this->result === false) {
                throw new RuntimeException("Database query failed: " . $this->wpdb->last_error);
            }

            return $this;
        } catch(Exception $e) {
            throw new RuntimeException("SELECT operation failed: " . $e->getMessage());
        }
    }

    /**
     * Returns the result of the executed query.
     *
     * @return array|null The query result.
     */
    public function getResult()
    {
        return $this->result;
    }
}
