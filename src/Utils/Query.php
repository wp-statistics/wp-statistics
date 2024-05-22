<?php 

namespace WP_Statistics\Utils;
use Exception;

class Query
{
    private $operation;
    private $table;
    private $fields = '*';
    private $whereClauses = [];

    public static function select($fields = '*')
    {
        $instance = new self();

        $instance->operation = "SELECT";
        $instance->fields    = $fields;

        return $instance;
    }

    public function fromTable($table)
    {
        $this->table = $table;
        return $this;
    }

    public function whereDate($field, $date)
    {

        if (is_array($date) && count($date) === 2) {
            $from = isset($date[0]) ? $date[0] : '';
            $to   = isset($date[1]) ? $date[1] : '';

            if (!empty($from) && !empty($to)) {
                $condition = "Date($field) BETWEEN '$from' AND '$to'";
            }
        }

        if (!empty($condition)) {
            $this->whereClauses[] = $condition;
        }

        return $this;
    }

    public function where($field, $operator, $value)
    {
        if (is_array($value)) {
            $value = array_filter($value);
        }

        if (empty($value)) $this;
    
        switch ($operator) {
            case '=':
            case '!=':
            case '>':
            case '>=':
            case '<':
            case '<=':
            case 'LIKE':
            case 'NOT LIKE':
                $condition = "$field $operator '$value'";
                break;

            case 'IN':
            case 'NOT IN':
                if (is_string($value)) {
                    $value = explode(',', $value);
                }

                if (is_array($value)) {
                    $items = implode(', ', array_map(function($item) { return "'$item'"; }, $value));
                    $condition = "$field $operator ($items)";
                }
                break;

            case 'BETWEEN':
                if (is_array($value) && count($value) === 2) {
                    $condition = "$field BETWEEN '$value[0]' AND '$value[1]'";
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

    public function get()
    {
        $query = "$this->operation $this->fields FROM $this->table";
        
        $clauses = array_filter($this->whereClauses);
    
        if (!empty($clauses)) {
            $query .= ' WHERE ' . implode(" AND ", $clauses);
        }

        return $query;
    }
}