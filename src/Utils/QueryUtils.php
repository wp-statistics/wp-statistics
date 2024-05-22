<?php 

namespace WP_Statistics\Utils;
use Exception;

class QueryUtils
{
     /**
     * Processes a condition and returns a SQL string representation of it.
     *
     * @param array $condition An associative array containing the condition details.
     *                         The array should have the following keys:
     *                         - field: The field name.
     *                         - value: The value to compare against.
     *                         - operator: The comparison operator. Defaults to '='
     * @return mixed The SQL string representation of the condition.
     * @throws \Exception If the operator is not supported.
     */
    private static function processCondition($condition) {
        $field      = $condition['field'];
        $value      = $condition['value'];
        $operator   = isset($condition['operator']) ? strtoupper($condition['operator']) : '=';

        if (is_array($value)) {
            $value = array_filter($value);
        }

        if (empty($value)) return;
    
        switch ($operator) {
            case '=':
            case '!=':
            case '>':
            case '>=':
            case '<':
            case '<=':
            case 'LIKE':
            case 'NOT LIKE':
                return "$field $operator '$value'";

            case 'IN':
            case 'NOT IN':
                if (is_string($value)) {
                    $value = explode(',', $value);
                }

                if (is_array($value)) {
                    $items = implode(', ', array_map(function($item) { return "'$item'"; }, $value));
                    return "$field $operator ($items)";
                }
                break;

            case 'BETWEEN':
                if (is_array($value) && count($value) === 2) {
                    if (!empty($value[0]) && !empty($value[1])) {
                        return "$field BETWEEN '$value[0]' AND '$value[1]'";
                    }
                }
                break;

            default:
                throw new Exception(esc_html__(sprintf("Unsupported operator: %s", $operator)));
        }
    }
    
    /**
     * Generates a WHERE clause for a SQL query based on the given conditions.
     *
     * @param array $conditions An array of conditions to generate the WHERE clause from.
     * @param string $relation (optional): The logical relation between the conditions. Defaults to 'AND'.
     * @return string The generated WHERE clause for the SQL query.
     */
    public static function whereClause($conditions, $relation = 'AND') {
    
        $sql     = '';
        $clauses = [];
        
        foreach ($conditions as $condition) {
            if (empty($condition['field']) || empty($condition['value'])) continue;

            $clauses[] = self::processCondition($condition);
        }

        $clauses = array_filter($clauses);
    
        if (!empty($clauses)) {
            $sql = ' WHERE ' . implode(" $relation ", $clauses);
        }

        return $sql;
    }
}