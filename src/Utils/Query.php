<?php

namespace WP_Statistics\Utils;

use WP_Statistics\Traits\Cacheable;
use WP_STATISTICS\DB;
use WP_STATISTICS\TimeZone;
use Exception;

class Query
{
    use Cacheable;

    private $operation;
    private $table;
    private $fields = '*';
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
        $instance->operation = "SELECT";
        $instance->fields    = $fields;
        return $instance;
    }

    private function getTable($table)
    {
        if (DB::table($table)) {
            $table = DB::table($table);
        } else {
            $table = $this->db->$table; 
        }

        return $table;
    }

    public function from($table)
    {
        $this->table = $this->getTable($table);
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
                throw new Exception(esc_html__(sprintf("Unsupported operator: %s", $operator)));
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

        $preparedQuery = $this->db->prepare($query, $this->whereValues);
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

        $preparedQuery = $this->db->prepare($query, $this->whereValues);
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

        $preparedQuery = $this->db->prepare($query, $this->whereValues);
        $result        = $this->db->get_col($preparedQuery);

        if (!$this->bypassCache) {
            $this->setCachedResult($query, $result);
        }

        return $result;
    }

    public function join($table, $condition, $joinType = 'INNER')
    {
        $table = $this->getTable($table);

        if (is_array($condition)) {
            $this->joinClauses[] = "{$joinType} JOIN {$table} ON {$condition[0]} = {$condition[1]}";
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

    public function getQuery()
    {
        return $this->buildQuery();
    }

    protected function buildQuery()
    {
        $query = "$this->operation $this->fields FROM $this->table";

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
