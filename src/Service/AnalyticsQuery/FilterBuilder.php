<?php

namespace WP_Statistics\Service\AnalyticsQuery;

use WP_Statistics\Service\AnalyticsQuery\Registry\FilterRegistry;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidFilterException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidOperatorException;

/**
 * Builds SQL WHERE clauses from filter objects.
 *
 * Converts frontend filter requests into safe SQL conditions.
 * Uses FilterRegistry for filter definitions to prevent SQL injection.
 *
 * @since 15.0.0
 */
class FilterBuilder
{

    /**
     * Build WHERE clauses from filters.
     *
     * @param array $filters Filter key-value pairs.
     * @return array ['conditions' => [], 'params' => [], 'joins' => []]
     * @throws InvalidFilterException
     * @throws InvalidOperatorException
     */
    public static function build(array $filters): array
    {
        $registry   = FilterRegistry::getInstance();
        $conditions = [];
        $params     = [];
        $joins      = [];

        foreach ($filters as $key => $value) {
            if (!$registry->has($key)) {
                throw new InvalidFilterException($key);
            }

            $filter = $registry->get($key);
            $column = $filter->getColumn();
            $type   = $filter->getType();

            // Collect required joins
            $filterJoins = $filter->getJoins();
            if ($filterJoins !== null) {
                foreach ($filterJoins as $join) {
                    $joins[$join['alias']] = $join;
                }
            }

            // Handle boolean special case (logged_in filter on user_id column)
            // For user_id: NULL or 0 = guest, > 0 = logged-in
            if ($type === 'boolean') {
                if ($value) {
                    $conditions[] = "($column IS NOT NULL AND $column != 0)";
                } else {
                    $conditions[] = "($column IS NULL OR $column = 0)";
                }
                continue;
            }

            // Handle operator syntax: { "contains": "google" }
            if (is_array($value) && !isset($value[0])) {
                $result       = self::buildOperatorCondition($column, $value, $type);
                $conditions[] = $result['condition'];
                if ($result['params'] !== null) {
                    if (is_array($result['params'])) {
                        $params = array_merge($params, $result['params']);
                    } else {
                        $params[] = $result['params'];
                    }
                }
            } else {
                // Simple equality: "country": "US" or array for IN: "country": ["US", "GB"]
                if (is_array($value)) {
                    $placeholders = implode(',', array_fill(0, count($value), '%s'));
                    $conditions[] = "$column IN ($placeholders)";
                    foreach ($value as $v) {
                        $params[] = self::sanitize($v, $type);
                    }
                } else {
                    $conditions[] = "$column = %s";
                    $params[]     = self::sanitize($value, $type);
                }
            }
        }

        return [
            'conditions' => $conditions,
            'params'     => $params,
            'joins'      => $joins,
        ];
    }

    /**
     * Build condition from operator syntax.
     *
     * @param string $column SQL column.
     * @param array  $value  Operator => operand.
     * @param string $type   Data type.
     * @return array ['condition' => string, 'params' => mixed]
     * @throws InvalidOperatorException
     */
    private static function buildOperatorCondition(string $column, array $value, string $type): array
    {
        $operator = key($value);
        $operand  = current($value);

        switch ($operator) {
            case 'is':
                return [
                    'condition' => "$column = %s",
                    'params'    => self::sanitize($operand, $type),
                ];

            case 'is_not':
                return [
                    'condition' => "$column != %s",
                    'params'    => self::sanitize($operand, $type),
                ];

            case 'in':
                if (!is_array($operand)) {
                    $operand = [$operand];
                }
                $placeholders = implode(',', array_fill(0, count($operand), '%s'));
                return [
                    'condition' => "$column IN ($placeholders)",
                    'params'    => array_map(function ($v) use ($type) {
                        return self::sanitize($v, $type);
                    }, $operand),
                ];

            case 'not_in':
                if (!is_array($operand)) {
                    $operand = [$operand];
                }
                $placeholders = implode(',', array_fill(0, count($operand), '%s'));
                return [
                    'condition' => "$column NOT IN ($placeholders)",
                    'params'    => array_map(function ($v) use ($type) {
                        return self::sanitize($v, $type);
                    }, $operand),
                ];

            case 'contains':
                return [
                    'condition' => "$column LIKE %s",
                    'params'    => '%' . self::sanitize($operand, 'string') . '%',
                ];

            case 'starts_with':
                return [
                    'condition' => "$column LIKE %s",
                    'params'    => self::sanitize($operand, 'string') . '%',
                ];

            case 'ends_with':
                return [
                    'condition' => "$column LIKE %s",
                    'params'    => '%' . self::sanitize($operand, 'string'),
                ];

            case 'gt':
                return [
                    'condition' => "$column > %d",
                    'params'    => self::sanitize($operand, 'integer'),
                ];

            case 'gte':
                return [
                    'condition' => "$column >= %d",
                    'params'    => self::sanitize($operand, 'integer'),
                ];

            case 'lt':
                return [
                    'condition' => "$column < %d",
                    'params'    => self::sanitize($operand, 'integer'),
                ];

            case 'lte':
                return [
                    'condition' => "$column <= %d",
                    'params'    => self::sanitize($operand, 'integer'),
                ];

            case 'before':
                return [
                    'condition' => "$column < %s",
                    'params'    => self::sanitize($operand, 'date'),
                ];

            case 'after':
                return [
                    'condition' => "$column > %s",
                    'params'    => self::sanitize($operand, 'date'),
                ];

            case 'between':
                if (!is_array($operand) || count($operand) < 2) {
                    throw new InvalidOperatorException('between requires an array with two values');
                }
                return [
                    'condition' => "$column BETWEEN %s AND %s",
                    'params'    => [
                        self::sanitize($operand[0], 'date'),
                        self::sanitize($operand[1], 'date'),
                    ],
                ];

            case 'is_not_empty':
                return [
                    'condition' => "($column IS NOT NULL AND $column != '')",
                    'params'    => null,
                ];

            case 'is_empty':
                return [
                    'condition' => "($column IS NULL OR $column = '')",
                    'params'    => null,
                ];

            default:
                throw new InvalidOperatorException($operator);
        }
    }

    /**
     * Sanitize a value based on type.
     *
     * @param mixed  $value Value to sanitize.
     * @param string $type  Data type.
     * @return mixed Sanitized value.
     */
    private static function sanitize($value, string $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'boolean':
                return $value ? 1 : 0;
            case 'date':
                // Validate and sanitize date format (Y-m-d or Y-m-d H:i:s)
                $value = sanitize_text_field($value);
                if (preg_match('/^\d{4}-\d{2}-\d{2}(\s\d{2}:\d{2}:\d{2})?$/', $value)) {
                    return $value;
                }
                return '';
            case 'string':
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Check if a filter key is allowed.
     *
     * @param string $key Filter key.
     * @return bool
     */
    public static function isAllowed(string $key): bool
    {
        return FilterRegistry::getInstance()->has($key);
    }

    /**
     * Get filter configuration.
     *
     * @param string $key Filter key.
     * @return array|null
     */
    public static function getConfig(string $key): ?array
    {
        $filter = FilterRegistry::getInstance()->get($key);
        return $filter ? $filter->toArray() : null;
    }

    /**
     * Check if a filter requires specific table.
     *
     * @param string $key Filter key.
     * @return string|null Required table or null.
     */
    public static function getRequirement(string $key): ?string
    {
        return FilterRegistry::getInstance()->getRequirement($key);
    }

    /**
     * Check if any filter requires the views table.
     *
     * @param array $filters Filter key-value pairs.
     * @return bool True if views table is required.
     */
    public static function requiresViewsTable(array $filters): bool
    {
        return FilterRegistry::getInstance()->requiresViewsTable(array_keys($filters));
    }

    /**
     * Check if any filter requires the events table.
     *
     * @param array $filters Filter key-value pairs.
     * @return bool True if events table is required.
     */
    public static function requiresEventsTable(array $filters): bool
    {
        return FilterRegistry::getInstance()->requiresEventsTable(array_keys($filters));
    }
}
