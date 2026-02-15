<?php

namespace WP_Statistics\Service\Admin\AccessControl;

use WP_Statistics\Utils\User;

/**
 * Enforces access level restrictions on analytics queries.
 *
 * Hooks into the analytics query pipeline to:
 * - Inject author filter for own_content users (see only their posts)
 * - Block PII/visitor queries for view_stats users
 *
 * @since 15.1.0
 */
class AccessLevelEnforcer
{
    /**
     * Group-by values that expose individual visitor data (PII).
     *
     * @var string[]
     */
    private const PII_GROUP_BY = ['visitor'];

    public function __construct()
    {
        add_filter('wp_statistics_analytics_query_data', [$this, 'enforceAccessLevel']);
    }

    /**
     * Modify or block analytics query data based on user access level.
     *
     * @param array $query Query data.
     * @return array Modified query data (or blocked query with _blocked key).
     */
    public function enforceAccessLevel(array $query): array
    {
        $level = User::getAccessLevel();

        // Manage and view_all users have unrestricted access
        if (AccessLevel::isAtLeast($level, AccessLevel::VIEW_ALL)) {
            return $query;
        }

        // view_stats: block PII queries
        if ($level === AccessLevel::VIEW_STATS) {
            return $this->blockPiiQueries($query);
        }

        // own_content: inject author filter + block PII
        if ($level === AccessLevel::OWN_CONTENT) {
            $query = $this->blockPiiQueries($query);
            if (isset($query['_blocked'])) {
                return $query;
            }
            return $this->injectAuthorFilter($query);
        }

        // none: block everything (shouldn't reach here due to AjaxManager check)
        return [
            '_blocked' => [
                'code'    => 'no_access',
                'message' => __('You do not have permission to view statistics.', 'wp-statistics'),
            ],
        ];
    }

    /**
     * Block queries that request individual visitor data.
     *
     * Checks both single queries (group_by at root level) and
     * batch queries (group_by inside each queries[] item).
     *
     * @param array $query Query data.
     * @return array Original query or blocked query.
     */
    private function blockPiiQueries(array $query): array
    {
        // Check single query group_by
        if ($this->hasPiiGroupBy($query)) {
            return [
                '_blocked' => [
                    'code'    => 'pii_restricted',
                    'message' => __('You do not have permission to view individual visitor data.', 'wp-statistics'),
                ],
            ];
        }

        // Check batch queries
        if (!empty($query['queries']) && is_array($query['queries'])) {
            foreach ($query['queries'] as $subQuery) {
                if ($this->hasPiiGroupBy($subQuery)) {
                    return [
                        '_blocked' => [
                            'code'    => 'pii_restricted',
                            'message' => __('You do not have permission to view individual visitor data.', 'wp-statistics'),
                        ],
                    ];
                }
            }
        }

        return $query;
    }

    /**
     * Check if a query uses PII-level group_by values.
     *
     * @param array $query Single query data.
     * @return bool
     */
    private function hasPiiGroupBy(array $query): bool
    {
        $groupBy = $query['group_by'] ?? [];
        if (!is_array($groupBy)) {
            return false;
        }

        return !empty(array_intersect($groupBy, self::PII_GROUP_BY));
    }

    /**
     * Inject author filter to scope data to current user's content.
     *
     * Adds an author filter for both single queries and batch queries.
     *
     * @param array $query Query data.
     * @return array Modified query with author filter.
     */
    private function injectAuthorFilter(array $query): array
    {
        $userId = get_current_user_id();

        // Single query: inject at root level
        if (!isset($query['queries'])) {
            $query = $this->addAuthorFilterToQuery($query, $userId);
            return $query;
        }

        // Batch query: inject as global filter
        $query['filters'] = $query['filters'] ?? [];
        if (is_array($query['filters'])) {
            $query['filters'] = $this->mergeAuthorFilter($query['filters'], $userId);
        }

        return $query;
    }

    /**
     * Add author filter to a single query's filters array.
     *
     * @param array $query  Single query data.
     * @param int   $userId Current user ID.
     * @return array Modified query.
     */
    private function addAuthorFilterToQuery(array $query, int $userId): array
    {
        $query['filters'] = $query['filters'] ?? [];
        if (is_array($query['filters'])) {
            $query['filters'] = $this->mergeAuthorFilter($query['filters'], $userId);
        }
        return $query;
    }

    /**
     * Merge author filter into an existing filters array.
     *
     * Uses the AnalyticsQuery filter format: { field, operator, value }.
     *
     * @param array $filters Existing filters.
     * @param int   $userId  User ID to filter by.
     * @return array Filters with author constraint added.
     */
    private function mergeAuthorFilter(array $filters, int $userId): array
    {
        // Check if filters is associative (key-value) or sequential (array of objects)
        if ($this->isSequentialFilterFormat($filters)) {
            $filters[] = [
                'field'    => 'author',
                'operator' => 'is',
                'value'    => (string) $userId,
            ];
        } else {
            // Associative format
            $filters['author'] = $userId;
        }

        return $filters;
    }

    /**
     * Check if filters use the sequential format [{field, operator, value}].
     *
     * @param array $filters Filters array.
     * @return bool
     */
    private function isSequentialFilterFormat(array $filters): bool
    {
        if (empty($filters)) {
            return false;
        }

        $first = reset($filters);
        return is_array($first) && isset($first['field']);
    }
}
