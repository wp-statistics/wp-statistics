<?php

namespace WP_Statistics\Utils;

use WP_Statistics\Traits\Cacheable;
use WP_STATISTICS\DB;
use Exception;

class Query
{
    use Cacheable;

    private $operation;
    private $table;
    private $fields = '*';
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

    public function fromTable($table)
    {

        if (DB::table($table)) {
            $this->table = DB::table($table);
        } else {
            $this->table = $this->db->$table; 
        }
        
        return $this;
    }

    public function whereDate($field, $date)
    {
        if (is_array($date) && count($date) === 2) {
            $from = isset($date[0]) ? $date[0] : '';
            $to   = isset($date[1]) ? $date[1] : '';

            if (!empty($from) && !empty($to)) {
                $condition            = "DATE($field) BETWEEN %s AND %s";
                $this->whereClauses[] = $condition;
                $this->whereValues[]  = $from;
                $this->whereValues[]  = $to;
            }
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

    public function distinct()
    {
        $this->fields = "DISTINCT {$this->fields}";
        return $this;
    }

    public function bypassCache($flag = true)
    {
        $this->bypassCache = $flag;
        return $this;
    }

    public function getFirst()
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

    public function getCount()
    {
        $this->operation = 'SELECT';
        $this->fields    = "COUNT({$this->fields})";
        
        $query = $this->buildQuery();

        if (!$this->bypassCache) {
            $cachedResult = $this->getCachedResult($query);
            if ($cachedResult !== false) {
                return (int) $cachedResult;
            }
        }

        $preparedQuery = $this->db->prepare($query, $this->whereValues);
        $result        = $this->db->get_var($preparedQuery);

        if (!$this->bypassCache) {
            $this->setCachedResult($query, $result);
        }

        return (int) $result;
    }

    protected function buildQuery()
    {
        $query = "$this->operation $this->fields FROM $this->table";

        $clauses = array_filter($this->whereClauses);

        if (!empty($clauses)) {
            $query .= ' WHERE ' . implode(" AND ", $clauses);
        }

        return $query;
    }
}
