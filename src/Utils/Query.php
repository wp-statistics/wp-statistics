<?php

namespace WP_Statistics\Utils;

use WP_Statistics\Traits\TransientCacheTrait;
use WP_STATISTICS\DB;
use WP_STATISTICS\TimeZone;
use InvalidArgumentException;

class Query
{
    use TransientCacheTrait;

    private $operation;
    private $table;
    private $fields = '*';
    private $subQuery;
    private $orderClause;
    private $groupByClause;
    private $limitClause;
    private $whereRelation = 'AND';
    private $setClauses = [];
    private $joinClauses = [];
    private $whereClauses = [];
    private $rawWhereClause = [];
    private $valuesToPrepare = [];
    private $allowCaching = false;

    /** @var wpdb $db */
    protected $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public static function select($fields = '*')
    {
        $instance            = new self();
        $instance->operation = 'select';

        if (is_array($fields)) {
            $fields = implode(', ', $fields);
        }

        $instance->fields = $fields;

        return $instance;
    }

    public static function update($table)
    {
        $instance            = new self();
        $instance->operation = 'update';
        $instance->table     = $instance->getTable($table);

        return $instance;
    }


    public function set($values)
    {
        if (empty($values)) return $this;

        foreach ($values as $field => $value) {
            if (is_string($value)) {
                $this->setClauses[]         = '%i = %s';
                $this->valuesToPrepare[]    = $field;
                $this->valuesToPrepare[]    = $value;
            } else if (is_numeric($value)) {
                $this->setClauses[]         = '%i = %d';
                $this->valuesToPrepare[]    = $field;
                $this->valuesToPrepare[]    = $value;
            } else if (is_null($value)) {
                $this->setClauses[]         = '%i = NULL';
                $this->valuesToPrepare[]    = $field;
            }
        }

        return $this;
    }


    private function getTable($table)
    {
        if (DB::table($table)) {
            $table = DB::table($table);
        } else {
            $table = "{$this->db->prefix}{$table}";
        }

        return $table;
    }

    /**
     * Sets the table for the query.
     *
     * @param string $table The name of the table.
     */
    public function from($table)
    {
        $this->table = $this->getTable($table);
        return $this;
    }

    /**
     * Sets the sub-query for the query.
     * Useful for times we want to get fields from a certain sub-query, not table.
     *
     * @param string $subQuery The subquery to be used in the query.
     * @param string $alias The alias to be assigned to the subquery. Defaults to 'sub_query'.
     */
    public function fromQuery($subQuery, $alias = 'sub_query')
    {
        $this->subQuery = "($subQuery) AS {$alias}";
        return $this;
    }

    /**
     * Filters the records based on the given date range for a specific field.
     *
     * @param string $field The name of the field to filter by.
     * @param mixed $date The date range to filter by. Either an array of date range, or string date.
     *
     * @example [2024-01-01, 2024-01-31]
     * @example 'today', 'yesterday', 'year', etc...
     * @see TimeZone::getDateFilters() for a list of all supported string dates
     */
    public function whereDate($field, $date)
    {
        if (empty($date)) return $this;

        if (is_array($date)) {
            $from = isset($date['from']) ? $date['from'] : '';
            $to   = isset($date['to']) ? $date['to'] : '';
        }

        if (is_string($date)) {
            $date = TimeZone::calculateDateFilter($date);
            $from = $date['from'];
            $to   = $date['to'];
        }

        if (!empty($from) && !empty($to)) {
            $condition                  = "DATE($field) BETWEEN %s AND %s";
            $this->whereClauses[]       = $condition;
            $this->valuesToPrepare[]    = $from;
            $this->valuesToPrepare[]    = $to;
        }

        // Determine whether caching should be allowed based on the date range
        $this->canUseCacheForDateRange($to);

        return $this;
    }

    public function whereRaw($condition, $values = [])
    {

        if (!empty($values)) {
            $this->rawWhereClause[] = $this->prepareQuery($condition, $values);
        } else {
            $this->rawWhereClause[] = $condition;
        }

        return $this;
    }

    /**
     * Filters the records based on the given condition.
     *
     * @param string $field The name of the field to filter by.
     * @param string $operator The operator to use for the condition. Supported operators are: '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN'.
     * @param mixed $value The value to compare against. For operators 'IN' and 'NOT IN', an array of values can be provided. For operator 'BETWEEN', an array of two values representing the range can be provided.
     */
    public function where($field, $operator, $value)
    {
        if (is_array($value)) {
            $value = array_filter($value);
        }

        // If the value is empty, we don't need to add it to the query (except for numbers)
        if (!is_numeric($value) && empty($value)) return $this;

        $condition = $this->generateCondition($field, $operator, $value);

        if (!empty($condition)) {
            $this->whereClauses[]  = $condition['condition'];
            $this->valuesToPrepare = array_merge($this->valuesToPrepare, $condition['values']);
        }

        return $this;
    }

    public function whereNotNull($fields)
    {
        if (empty($fields)) return $this;

        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }

        if (is_array($fields)) {
            foreach ($fields as $field) {
                $this->whereClauses[] = "{$field} IS NOT NULL";
                $this->whereClauses[] = "{$field} != ''";
            }
        }

        return $this;
    }

    public function whereNull($fields)
    {
        if (empty($fields)) return $this;

        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }

        if (is_array($fields)) {
            foreach ($fields as $field) {
                $this->whereClauses[] = "{$field} IS NULL";
                $this->whereClauses[] = "{$field} = ''";
            }
        }

        return $this;
    }


    public function whereRelation($relation)
    {
        if (in_array($relation, ['AND', 'OR'])) {
            $this->whereRelation = $relation;
        }

        return $this;
    }

    protected function generateCondition($field, $operator, $value)
    {
        $condition = '';
        $values    = [];

        switch ($operator) {
            case '=':
            case '!=':
            case '>':
            case '>=':
            case '<':
            case '<=':
            case 'LIKE':
            case 'NOT LIKE':
                if (is_numeric($value) || !empty($value)) {
                    $condition = "$field $operator %s";
                    $values[]  = $value;
                }
                break;

            case 'IN':
            case 'NOT IN':
                if (is_string($value)) {
                    $value = explode(',', $value);
                }

                if (!empty($value) && is_array($value)) {
                    $placeholders = implode(', ', array_fill(0, count($value), '%s'));
                    $condition    = "$field $operator ($placeholders)";
                    $values       = $value;
                }
                break;

            case 'BETWEEN':
                if (is_array($value) && count($value) === 2) {
                    $condition = "$field BETWEEN %s AND %s";
                    $values    = $value;
                }
                break;

            default:
                throw new InvalidArgumentException(esc_html__(sprintf("Unsupported operator: %s", $operator)));
        }

        if (empty($condition)) return;

        return [
            'condition' => $condition,
            'values'    => $values
        ];
    }

    public function getVar()
    {
        $query = $this->buildQuery();
        $query = $this->prepareQuery($query, $this->valuesToPrepare);

        if ($this->allowCaching) {
            $cachedResult = $this->getCachedResult($query);
            if ($cachedResult !== false) {
                return $cachedResult;
            }
        }

        $result = $this->db->get_var($query);

        if ($this->allowCaching) {
            $this->setCachedResult($query, $result);
        }

        return $result;
    }

    public function getAll()
    {
        $query = $this->buildQuery();
        $query = $this->prepareQuery($query, $this->valuesToPrepare);

        if ($this->allowCaching) {
            $cachedResult = $this->getCachedResult($query);
            if ($cachedResult !== false) {
                return $cachedResult;
            }
        }

        $result = $this->db->get_results($query);

        if ($this->allowCaching) {
            $this->setCachedResult($query, $result);
        }

        return $result;
    }

    public function getCol()
    {
        $query = $this->buildQuery();
        $query = $this->prepareQuery($query, $this->valuesToPrepare);

        if ($this->allowCaching) {
            $cachedResult = $this->getCachedResult($query);
            if ($cachedResult !== false) {
                return $cachedResult;
            }
        }

        $result = $this->db->get_col($query);

        if ($this->allowCaching) {
            $this->setCachedResult($query, $result);
        }

        return $result;
    }

    public function getRow()
    {
        $query = $this->buildQuery();
        $query = $this->prepareQuery($query, $this->valuesToPrepare);

        if ($this->allowCaching) {
            $cachedResult = $this->getCachedResult($query);
            if ($cachedResult !== false) {
                return $cachedResult;
            }
        }

        $result = $this->db->get_row($query);

        if ($this->allowCaching) {
            $this->setCachedResult($query, $result);
        }

        return $result;
    }

    public function execute()
    {
        $query  = $this->buildQuery();
        $query  = $this->prepareQuery($query, $this->valuesToPrepare);
        $result = $this->db->query($query);

        return $result;
    }


    /**
     * Joins the current table with another table based on a given condition.
     *
     * @param string $table The name of the table to join with.
     * @param array|string $on Table keys to join. Acceptable formats: `['table1.primary_key', 'table2.foreign_key']` OR `'table1.primary_key = table2.foreign_key'`.
     * @param array[] $conditions Array of extra join conditions to append. [['field', 'operator', 'value'], ...]
     * @param string $joinType The type of join to perform. Defaults to 'INNER'.
     *
     * @throws InvalidArgumentException If the join clause is invalid.
     */
    public function join($table, $on, $conditions = [], $joinType = 'INNER')
    {
        $joinTable = $this->getTable($table);

        if ((is_array($on) && count($on) == 2) || is_string($on)) {
            $joinClause = "{$joinType} JOIN {$joinTable} AS $table ON ";
            $joinClause .= is_array($on) ? "{$on[0]} = {$on[1]}" : "{$on}";

            if (!empty($conditions)) {
                foreach ($conditions as $condition) {
                    $field    = $condition[0];
                    $operator = $condition[1];
                    $value    = $condition[2];

                    $condition = $this->generateCondition($field, $operator, $value);

                    if (!empty($condition)) {
                        $condition  = $this->prepareQuery($condition['condition'], $condition['values']);
                        $joinClause .= " AND {$condition}";
                    }
                }
            }

            $this->joinClauses[] = $joinClause;
        } else {
            throw new InvalidArgumentException(esc_html__('Invalid join clause', 'wp-statistics'));
        }

        return $this;
    }

    /**
     * Joins the current query with a subquery based on a given condition.
     *
     * @param string $subQuery The subquery to join with.
     * @param array $on Array of table keys to join. The array should contain two elements: the first element is the primary key of the current table, and the second element is the foreign key of the subquery table.
     * @param string $alias The alias to be assigned to the subquery.
     * @param string $joinType The type of join to perform. Defaults to 'INNER'.
     *
     * @throws InvalidArgumentException If the join clause is invalid.
     */
    public function joinQuery($subQuery, $on, $alias, $joinType = 'INNER')
    {
        if (is_array($on) && count($on) == 2) {
            $joinClause          = "{$joinType} JOIN ({$subQuery}) AS {$alias} ON {$on[0]} = {$on[1]}";
            $this->joinClauses[] = $joinClause;
        } else {
            throw new InvalidArgumentException(esc_html__('Invalid join clause', 'wp-statistics'));
        }

        return $this;
    }

    public function orderBy($fields, $order = 'DESC')
    {
        // Validate $order
        if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        if (!empty($fields)) {
            if (is_string($fields)) {
                $fields = explode(',', $fields);
                $fields = array_map('trim', $fields);
            }

            if (is_array($fields)) {
                $placeholders = [];
                $values       = [];

                // For identifiers with a dot (e.g. table.field) we need to split the identifier into two parts
                foreach ($fields as $field) {
                    if (strpos($field, '.') !== false) {
                        $identifier  = explode('.', $field);
                        $values      = array_merge($values, $identifier);
                        $placeholder = '%i.%i';
                    } else {
                        $values[]    = $field;
                        $placeholder = '%i';
                    }

                    $placeholders[] = "$placeholder $order";
                }

                $placeholders = implode(', ', $placeholders);
            }

            $this->orderClause = $this->prepareQuery("ORDER BY {$placeholders}", $values);
        }

        return $this;
    }

    public function perPage($page = 1, $perPage = 10)
    {
        $page    = intval($page);
        $perPage = intval($perPage);

        if ($page > 0 && $perPage > 0) {
            $offset            = ($page - 1) * $perPage;
            $this->limitClause = "LIMIT {$perPage} OFFSET {$offset}";
        }

        return $this;
    }

    public function groupBy($fields)
    {
        if (is_array($fields)) {
            $fields = implode(', ', $fields);
        }

        if (!empty($fields)) {
            $this->groupByClause = "GROUP BY {$fields}";
        }

        return $this;
    }

    protected function buildQuery()
    {
        $queryMethod = "{$this->operation}Query";

        if (method_exists($this, $queryMethod)) {
            $query = $this->$queryMethod();
        } else {
            throw new InvalidArgumentException(sprintf(esc_html__('%s method is not defined.', 'wp-statistics'), $queryMethod));
        }

        return $query;
    }

    public function getQuery()
    {
        $query         = $this->buildQuery();
        $preparedQuery = $this->prepareQuery($query, $this->valuesToPrepare);

        return $preparedQuery;
    }

    protected function prepareQuery($query, $args = [])
    {
        // Only if there's a placeholder, prepare the query
        if (preg_match('/%[i|s|f|d]/', $query)) {
            $query = $this->db->prepare($query, $args);
        }

        return $query;
    }

    protected function selectQuery()
    {
        $query = "SELECT $this->fields FROM ";

        // Append table
        if (!empty($this->table)) {
            $query .= ' ' . $this->table;
            $query .= ' AS ' . $this->removeTablePrefix($this->table);
        }

        // Append sub query
        if (!empty($this->subQuery)) {
            $query .= ' ' . $this->subQuery;
        }

        // Append JOIN clauses
        $joinClauses = array_filter($this->joinClauses);
        if (!empty($joinClauses)) {
            $query .= ' ' . implode(' ', $joinClauses);
        }

        // Append WHERE clauses
        $whereClauses = array_filter($this->whereClauses);
        if (!empty($whereClauses)) {
            $query .= ' WHERE ' . implode(" $this->whereRelation ", $whereClauses);
        }

        if (!empty($this->rawWhereClause)) {
            $query .= empty($this->whereClauses) ? ' WHERE ' : ' ';
            $query .= implode(' ', $this->rawWhereClause);
        }

        // Append GROUP BY clauses
        if (!empty($this->groupByClause)) {
            $query .= ' ' . $this->groupByClause;
        }

        // Append ORDER clauses
        if (!empty($this->orderClause)) {
            $query .= ' ' . $this->orderClause;
        }

        // Append LIMIT clauses
        if (!empty($this->limitClause)) {
            $query .= ' ' . $this->limitClause;
        }

        return $query;
    }

    protected function updateQuery()
    {
        $query = "UPDATE $this->table";

        if (!empty($this->setClauses)) {
            $query .= ' SET ' . implode(', ', $this->setClauses);
        }

        // Append WHERE clauses
        $whereClauses = array_filter($this->whereClauses);
        if (!empty($whereClauses)) {
            $query .= ' WHERE ' . implode(" $this->whereRelation ", $whereClauses);
        }

        if (!empty($this->rawWhereClause)) {
            $query .= empty($this->whereClauses) ? ' WHERE ' : ' ';
            $query .= implode(' ', $this->rawWhereClause);
        }

        return $query;
    }

    /**
     * @return $this
     * @deprecated Use allowCaching() instead.
     */
    public function bypassCache()
    {
        return $this;
    }

    /**
     * Allow caching for the query.
     *
     * @param $flag
     * @return $this
     */
    public function allowCaching($flag = true)
    {
        $this->allowCaching = $flag;
        return $this;
    }

    public function removeTablePrefix($query)
    {
        return str_replace([$this->db->prefix, 'statistics_'], '', $query);
    }

    /**
     * Determine whether caching is permissible based on the specified date range.
     *
     * @param string $to The end date of the range.
     * @return void
     */
    protected function canUseCacheForDateRange($to)
    {
        $today = date('Y-m-d');

        // Cache should be used if the date range does not include today
        if ($to < $today) {
            $this->allowCaching();
        }
    }
}
