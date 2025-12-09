<?php

namespace WP_Statistics\Service\AnalyticsQuery;

use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidFilterException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidOperatorException;

/**
 * Builds SQL WHERE clauses from filter objects.
 *
 * Converts frontend filter requests into safe SQL conditions.
 * All filter columns are whitelisted to prevent SQL injection.
 *
 * @since 15.0.0
 */
class FilterBuilder
{
    /**
     * Allowed filter definitions.
     *
     * Each filter has:
     * - column: SQL column expression
     * - type: Data type for sanitization (string, integer, boolean, float)
     * - join: Optional join required for this filter
     *
     * @var array
     */
    private static $allowedFilters = [
        'country' => [
            'column' => 'countries.code',
            'type'   => 'string',
            'join'   => [
                'table' => 'countries',
                'alias' => 'countries',
                'on'    => 'sessions.country_id = countries.ID',
            ],
        ],
        'country_id' => [
            'column' => 'sessions.country_id',
            'type'   => 'integer',
        ],
        'city' => [
            'column' => 'cities.city_name',
            'type'   => 'string',
            'join'   => [
                'table' => 'cities',
                'alias' => 'cities',
                'on'    => 'sessions.city_id = cities.ID',
            ],
        ],
        'browser' => [
            'column' => 'device_browsers.name',
            'type'   => 'string',
            'join'   => [
                'table' => 'device_browsers',
                'alias' => 'device_browsers',
                'on'    => 'sessions.device_browser_id = device_browsers.ID',
            ],
        ],
        'os' => [
            'column' => 'device_oss.name',
            'type'   => 'string',
            'join'   => [
                'table' => 'device_oss',
                'alias' => 'device_oss',
                'on'    => 'sessions.device_os_id = device_oss.ID',
            ],
        ],
        'device_type' => [
            'column' => 'device_types.name',
            'type'   => 'string',
            'join'   => [
                'table' => 'device_types',
                'alias' => 'device_types',
                'on'    => 'sessions.device_type_id = device_types.ID',
            ],
        ],
        'referrer' => [
            'column' => 'referrers.domain',
            'type'   => 'string',
            'join'   => [
                'table' => 'referrers',
                'alias' => 'referrers',
                'on'    => 'sessions.referrer_id = referrers.ID',
            ],
        ],
        'referrer_type' => [
            'column' => 'referrers.channel',
            'type'   => 'string',
            'join'   => [
                'table' => 'referrers',
                'alias' => 'referrers',
                'on'    => 'sessions.referrer_id = referrers.ID',
            ],
        ],
        'post_type' => [
            'column'   => 'resources.resource_type',
            'type'     => 'string',
            'join'     => [
                [
                    'table' => 'resource_uris',
                    'alias' => 'resource_uris',
                    'on'    => 'views.resource_uri_id = resource_uris.ID',
                ],
                [
                    'table' => 'resources',
                    'alias' => 'resources',
                    'on'    => 'resource_uris.resource_id = resources.ID',
                ],
            ],
            'requires' => 'views',
        ],
        'author_id' => [
            'column'   => 'resources.cached_author_id',
            'type'     => 'integer',
            'join'     => [
                [
                    'table' => 'resource_uris',
                    'alias' => 'resource_uris',
                    'on'    => 'views.resource_uri_id = resource_uris.ID',
                ],
                [
                    'table' => 'resources',
                    'alias' => 'resources',
                    'on'    => 'resource_uris.resource_id = resources.ID',
                ],
            ],
            'requires' => 'views',
        ],
        'user_id' => [
            'column' => 'sessions.user_id',
            'type'   => 'integer',
        ],
        'logged_in' => [
            'column' => 'sessions.user_id',
            'type'   => 'boolean',
        ],
        'page' => [
            'column'   => 'resource_uris.uri',
            'type'     => 'string',
            'join'     => [
                'table' => 'resource_uris',
                'alias' => 'resource_uris',
                'on'    => 'views.resource_uri_id = resource_uris.ID',
            ],
            'requires' => 'views',
        ],
        'resource_id' => [
            'column'   => 'views.resource_id',
            'type'     => 'integer',
            'requires' => 'views',
        ],
        'language' => [
            'column' => 'languages.code',
            'type'   => 'string',
            'join'   => [
                'table' => 'languages',
                'alias' => 'languages',
                'on'    => 'sessions.language_id = languages.ID',
            ],
        ],
        'visitor_id' => [
            'column' => 'sessions.visitor_id',
            'type'   => 'integer',
        ],
        'session_id' => [
            'column' => 'sessions.ID',
            'type'   => 'integer',
        ],
    ];

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
        $conditions = [];
        $params     = [];
        $joins      = [];

        foreach ($filters as $key => $value) {
            if (!isset(self::$allowedFilters[$key])) {
                throw new InvalidFilterException($key);
            }

            $config = self::$allowedFilters[$key];
            $column = $config['column'];

            // Collect required joins
            if (isset($config['join'])) {
                $filterJoins = isset($config['join']['table']) ? [$config['join']] : $config['join'];
                foreach ($filterJoins as $join) {
                    $joins[$join['alias']] = $join;
                }
            }

            // Handle boolean special case (logged_in)
            if ($config['type'] === 'boolean') {
                if ($value) {
                    $conditions[] = "$column IS NOT NULL";
                } else {
                    $conditions[] = "$column IS NULL";
                }
                continue;
            }

            // Handle operator syntax: { "contains": "google" }
            if (is_array($value) && !isset($value[0])) {
                $result       = self::buildOperatorCondition($column, $value, $config['type']);
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
                        $params[] = self::sanitize($v, $config['type']);
                    }
                } else {
                    $conditions[] = "$column = %s";
                    $params[]     = self::sanitize($value, $config['type']);
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
        return isset(self::$allowedFilters[$key]);
    }

    /**
     * Get filter configuration.
     *
     * @param string $key Filter key.
     * @return array|null
     */
    public static function getConfig(string $key): ?array
    {
        return self::$allowedFilters[$key] ?? null;
    }

    /**
     * Check if a filter requires specific table.
     *
     * @param string $key Filter key.
     * @return string|null Required table or null.
     */
    public static function getRequirement(string $key): ?string
    {
        if (!isset(self::$allowedFilters[$key])) {
            return null;
        }

        return self::$allowedFilters[$key]['requires'] ?? null;
    }

    /**
     * Check if any filter requires the views table.
     *
     * @param array $filters Filter key-value pairs.
     * @return bool True if views table is required.
     */
    public static function requiresViewsTable(array $filters): bool
    {
        foreach (array_keys($filters) as $key) {
            if (self::getRequirement($key) === 'views') {
                return true;
            }
        }

        return false;
    }
}
