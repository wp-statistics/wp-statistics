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
     * Generate the full table name with prefix for JOIN operations.
     *
     * @param string $tableName The raw table name without prefix.
     * @return string The full prefixed table name.
     * @throws \InvalidArgumentException If the table name is empty.
     */
    protected function getFullJoinTableName($tableName)
    {
        if (empty($tableName)) {
            throw new \InvalidArgumentException('Join table name must be set before proceeding.');
        }

        /**
         * Filter the prefix segment used between wpdb prefix and table name.
         *
         * @param string $prefix The default 'statistics' (no trailing underscore).
         * @param string $tableName The logical table name.
         */
        $tableNamePrefix = apply_filters('wp_statistics_table_prefix', 'statistics', $tableName);

        return $this->wpdb->prefix . $tableNamePrefix . '_' . $tableName;
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
            $sql     = "SELECT {$columns} FROM {$this->fullName}";

            if (!empty($this->args['joins']) && is_array($this->args['joins'])) {
                foreach ($this->args['joins'] as $join) {
                    if (!isset($join['table'], $join['on'], $join['type'])) {
                        throw new RuntimeException("Invalid JOIN configuration.");
                    }

                    $joinTable = $this->getFullJoinTableName($join['table']);
                    $joinAlias = isset($join['alias']) ? $join['alias'] : $join['table'];

                    $sql .= " {$join['type']} JOIN {$joinTable} AS {$joinAlias} ON {$join['on']}";
                }
            }

            // Add WHERE clause if provided
            $params       = [];
            $whereClauses = [];
            $connector    = strtoupper($this->args['raw_where_type'] ?? 'AND');

            if (!empty($this->args['where'])) {
                foreach ($this->args['where'] as $column => $value) {
                    $whereClauses[] = "`$column` = %s";
                    $params[]       = $value;
                }
            }

            // Add WHERE IN clause if provided
            if (!empty($this->args['where_in'])) {
                foreach ($this->args['where_in'] as $column => $values) {
                    if (!is_array($values) || empty($values)) {
                        throw new RuntimeException("Invalid value for WHERE IN clause.");
                    }

                    $placeholders   = implode(',', array_fill(0, count($values), '%s'));
                    $whereClauses[] = "`$column` IN ($placeholders)";
                    foreach ($values as $value) {
                        $params[] = $value;
                    }
                }
            }

            if (!empty($this->args['raw_where']) && is_array($this->args['raw_where'])) {
                foreach ($this->args['raw_where'] as $condition) {
                    if (!empty($condition) && is_string($condition)) {
                        $whereClauses[] = "($condition)";
                    }
                }
            }

            // Add WHERE conditions to SQL query
            if (!empty($whereClauses)) {
                $sql .= ' WHERE ' . implode(" $connector ", $whereClauses);
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
                $sql      .= " LIMIT %d OFFSET %d";
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
        } catch (Exception $e) {
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
