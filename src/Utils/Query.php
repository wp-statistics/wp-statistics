<?php

namespace WP_Statistics\Utils;

use WP_Statistics\Traits\Cacheable;
use WP_STATISTICS\DB;
use WP_STATISTICS\TimeZone;
use InvalidArgumentException;

class Query
{
    use Cacheable;

    private $operation;
    private $table;
    private $fields = '*';
    private $subQuery;
    private $orderClause;
    private $groupByClause;
    private $limitClause;
    private $joinClauses = [];
    private $whereClauses = [];
    private $whereValues = [];
    private $bypassCache = false;

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
            $fields = implode(',', $fields);
        }

        $instance->fields = $fields;

        return $instance;
    }

    private function getTable($table)
    {
        if (DB::table($table)) {
            $table = DB::table($table);
        } else if (in_array($table, $this->db->tables)) {
            $table = "{$this->db->prefix}{$table}";
        }

        return $table ? $table : '';
    }

    public function fromTable($table)
    {
        $this->table = $this->getTable($table);
        return $this;
    }

    public function fromQuery($subQuery)
    {
        $this->subQuery = "($subQuery) as sub_query";
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
        if (is_array($date)) {
            $from = isset($date[0]) ? $date[0] : '';
            $to   = isset($date[1]) ? $date[1] : '';
        }

        if (is_string($date)) {
            $date = TimeZone::calculateDateFilter($date);
            $from = $date[0];
            $to   = $date[1];
        }

        if (!empty($from) && !empty($to)) {
            $condition            = "DATE($field) BETWEEN %s AND %s";
            $this->whereClauses[] = $condition;
            $this->whereValues[]  = $from;
            $this->whereValues[]  = $to;
        }

        return $this;
    }

    public function where($field, $operator, $value)
    {
        if (is_array($value)) {
            $value = array_filter($value);
        }

        if (empty($value)) return $this;

        $condition = '';
        switch ($operator) {
            case '=':
            case '!=':
            case '>':
            case '>=':
            case '<':
            case '<=':
            case 'LIKE':
            case 'NOT LIKE':
                $condition           = "$field $operator %s";
                $this->whereValues[] = $value;
                break;

            case 'IN':
            case 'NOT IN':
                if (is_string($value)) {
                    $value = explode(',', $value);
                }

                if (is_array($value)) {
                    $placeholders      = implode(', ', array_fill(0, count($value), '%s'));
                    $condition         = "$field $operator ($placeholders)";
                    $this->whereValues = array_merge($this->whereValues, $value);
                }
                break;

            case 'BETWEEN':
                if (is_array($value) && count($value) === 2) {
                    $condition           = "$field BETWEEN %s AND %s";
                    $this->whereValues[] = $value[0];
                    $this->whereValues[] = $value[1];
                }
                break;

            default:
                throw new InvalidArgumentException(esc_html__(sprintf("Unsupported operator: %s", $operator)));
        }

        if (!empty($condition)) {
            $this->whereClauses[] = $condition;
        }

        return $this;
    }

    public function getVar()
    {
        $query = $this->buildQuery();

        if (!$this->bypassCache) {
            $cachedResult = $this->getCachedResult($query);
            if ($cachedResult !== false) {
                return $cachedResult;
            }
        }

        $preparedQuery = $this->prepareQuery($query);
        $result        = $this->db->get_var($preparedQuery);

        if (!$this->bypassCache) {
            $this->setCachedResult($query, $result);
        }

        return $result;
    }

    public function getAll()
    {
        $query = $this->buildQuery();

        if (!$this->bypassCache) {
            $cachedResult = $this->getCachedResult($query);
            if ($cachedResult !== false) {
                return $cachedResult;
            }
        }

        $preparedQuery = $this->prepareQuery($query);
        $result        = $this->db->get_results($preparedQuery);

        if (!$this->bypassCache) {
            $this->setCachedResult($query, $result);
        }

        return $result;
    }

    public function getCol()
    {
        $query = $this->buildQuery();

        if (!$this->bypassCache) {
            $cachedResult = $this->getCachedResult($query);
            if ($cachedResult !== false) {
                return $cachedResult;
            }
        }

        $preparedQuery = $this->prepareQuery($query);
        $result        = $this->db->get_col($preparedQuery);

        if (!$this->bypassCache) {
            $this->setCachedResult($query, $result);
        }

        return $result;
    }

    /**
     * Joins the current table with another table based on a given condition.
     *
     * @param string $table The name of the table to join with.
     * @param array $condition An array with first item being the primary key in the first table and second item being the foreign key in the joined table.
     * @param string $joinType The type of join to perform. Defaults to 'INNER'.
     */
    public function join($table, $condition, $joinType = 'INNER')
    {
        $joinTable = $this->getTable($table);

        if (is_array($condition) && count($condition) == 2) {
            $primaryKey = "{$this->table}.{$condition[0]}";
            $foreignKey = "{$joinTable}.{$condition[1]}";

            $this->joinClauses[] = "{$joinType} JOIN {$joinTable} ON {$primaryKey} = {$foreignKey}";
        } else {
            throw new InvalidArgumentException(esc_html__('Invalid join clause', 'wp-statistics'));
        }
        
        return $this;
    }
    
    public function orderBy($field, $order = 'DESC')
    {
        $this->orderClause = "ORDER BY {$field} {$order}";
        
        return $this;
    }
    
    public function limit($limit, $offset = '')
    {
        $this->limitClause = "LIMIT {$limit}" . ($offset ? " OFFSET {$offset}" : '');
        
        return $this;
    }
    
    public function groupBy($fields)
    {
        if (is_array($fields)) {
            $fields = implode(', ', $fields);
        }

        $this->groupByClause = "GROUP BY {$fields}";
        
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
        $query          = $this->buildQuery();
        $preparedQuery  = $this->prepareQuery($query);

        return $preparedQuery;
    }

    protected function prepareQuery($query)
    {
        // Only if there's a placeholder, prepare the query
        if (preg_match('/%[i|s|f|d]/', $query)) {
            $query = $this->db->prepare($query, $this->whereValues);
        }

        return $query;
    }

    protected function selectQuery()
    {
        $query = "SELECT $this->fields FROM ";

        // Append table
        if (!empty($this->table)) {
            $query .= ' ' . $this->table;
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
            $query .= ' WHERE ' . implode(" AND ", $whereClauses);
        }

        // Append GROUP BY clauses
        if (!empty($this->groupByClause)) {
            $query .= ' ' . $this->groupByClause;
        }

        // Append LIMIT clauses
        if (!empty($this->limitClause)) {
            $query .= ' ' . $this->limitClause;
        }

        // Append ORDER clauses
        if (!empty($this->orderClause)) {
            $query .= ' ' . $this->orderClause;
        }

        return $query;
    }

    public function bypassCache($flag = true)
    {
        $this->bypassCache = $flag;
        return $this;
    }
}
