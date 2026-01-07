<?php

namespace WP_Statistics\Service\AnalyticsQuery;

use WP_Statistics\Service\AnalyticsQuery\Query\Query;
use WP_Statistics\Service\AnalyticsQuery\Query\QueryExecutor;
use WP_Statistics\Service\AnalyticsQuery\Registry\SourceRegistry;
use WP_Statistics\Service\AnalyticsQuery\Registry\GroupByRegistry;
use WP_Statistics\Service\AnalyticsQuery\Cache\CacheManager;
use WP_Statistics\Service\AnalyticsQuery\Comparison\ComparisonHandler;
use WP_Statistics\Service\AnalyticsQuery\Contracts\FormatterInterface;
use WP_Statistics\Service\AnalyticsQuery\Formatters\TableFormatter;
use WP_Statistics\Service\AnalyticsQuery\Formatters\FlatFormatter;
use WP_Statistics\Service\AnalyticsQuery\Formatters\ChartFormatter;
use WP_Statistics\Service\AnalyticsQuery\Formatters\ExportFormatter;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidSourceException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidGroupByException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidDateRangeException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidFormatException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidColumnException;
use WP_Statistics\Service\Admin\UserPreferences\UserPreferencesManager;

/**
 * Facade for analytics query operations.
 *
 * Provides a simplified interface for executing analytics queries,
 * orchestrating validation, caching, execution, and comparison.
 *
 * @since 15.0.0
 */
class AnalyticsQueryHandler
{
    /**
     * Query executor.
     *
     * @var QueryExecutor
     */
    private $executor;

    /**
     * Cache manager.
     *
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * Comparison handler.
     *
     * @var ComparisonHandler
     */
    private $comparisonHandler;

    /**
     * Source registry.
     *
     * @var SourceRegistry
     */
    private $sourceRegistry;

    /**
     * Group by registry.
     *
     * @var GroupByRegistry
     */
    private $groupByRegistry;

    /**
     * Response formatters indexed by format name.
     *
     * @var FormatterInterface[]
     */
    private $formatters = [];

    /**
     * Constructor.
     *
     * @param bool $enableCache Whether to enable caching.
     */
    public function __construct(bool $enableCache = true)
    {
        $this->sourceRegistry    = SourceRegistry::getInstance();
        $this->groupByRegistry   = GroupByRegistry::getInstance();
        $this->executor          = new QueryExecutor($this->sourceRegistry, $this->groupByRegistry);
        $this->cacheManager      = new CacheManager($enableCache);
        $this->comparisonHandler = new ComparisonHandler();

        // Initialize formatters
        $this->initializeFormatters();
    }

    /**
     * Initialize response formatters.
     *
     * @return void
     */
    private function initializeFormatters(): void
    {
        $formatters = [
            new TableFormatter($this->cacheManager),
            new FlatFormatter($this->cacheManager),
            new ChartFormatter($this->cacheManager),
            new ExportFormatter($this->cacheManager),
        ];

        foreach ($formatters as $formatter) {
            $this->formatters[$formatter->getName()] = $formatter;
        }
    }

    /**
     * Get formatter by name.
     *
     * @param string $format Format name.
     * @return FormatterInterface
     */
    private function getFormatter(string $format): FormatterInterface
    {
        return $this->formatters[$format] ?? $this->formatters['table'];
    }

    /**
     * Handle a single analytics query.
     *
     * @param array $request Query request.
     * @return array Response with data and meta.
     * @throws InvalidSourceException
     * @throws InvalidGroupByException
     * @throws InvalidDateRangeException
     */
    public function handle(array $request): array
    {
        // Extract context for user preferences (before normalizing)
        $context = $request['context'] ?? null;

        // Apply user preferences if context is provided
        if (!empty($context)) {
            $request = $this->applyUserPreferences($request, $context);
        }

        // Validate the request
        $this->validate($request);

        // Normalize request with defaults
        $request = $this->normalizeRequest($request);

        // Check cache first
        // $cached = $this->cacheManager->get($request);
        // if ($cached !== null) {
        //     $cached['meta']['cached'] = true;
        //     return $cached;
        // }

        // Create Query object
        $query = Query::fromArray($request);

        // Execute the query
        $result = $this->executeQuery($query);

        // Handle comparison if requested
        if ($query->hasComparison()) {
            $result = $this->addComparison($query, $result);
        }

        // Build response
        $response = $this->buildResponse($query, $result);

        // Add user preferences to response meta if context is provided
        $response = $this->addUserPreferences($response, $context);

        // Cache the result
        $this->cacheManager->set($request, $response);

        return $response;
    }

    /**
     * Handle a batch of analytics queries.
     *
     * Supports global parameters that apply to all queries:
     * - date_from/date_to: Inherited unless query provides both
     * - previous_date_from/previous_date_to: Custom comparison period (inherited unless query provides both)
     * - filters: Merged with query-specific filters (query values override)
     * - compare: Applied to queries that don't explicitly set it
     * - page_context: If provided, loads page preferences and skips hidden widgets
     *
     * @param array       $queries                Array of queries with 'id' field.
     * @param string|null $dateFrom               Global date_from (queries can override).
     * @param string|null $dateTo                 Global date_to (queries can override).
     * @param array       $globalFilters          Global filters (merged with query filters).
     * @param bool        $globalCompare          Global compare flag (queries can override).
     * @param string|null $pageContext            Page context for widget visibility preferences.
     * @param string|null $globalPreviousDateFrom Global previous_date_from for custom comparison period.
     * @param string|null $globalPreviousDateTo   Global previous_date_to for custom comparison period.
     * @return array Response with results keyed by query ID.
     */
    public function handleBatch(
        array $queries,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        array $globalFilters = [],
        bool $globalCompare = false,
        ?string $pageContext = null,
        ?string $globalPreviousDateFrom = null,
        ?string $globalPreviousDateTo = null
    ): array {
        $results        = [];
        $errors         = [];
        $skippedQueries = [];

        // Validate batch limits
        if (count($queries) > 20) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'batch_limit_exceeded',
                    'message' => __('Maximum 20 queries per batch allowed.', 'wp-statistics'),
                ],
            ];
        }

        // If page_context is provided, fetch page preferences to check widget visibility
        $pagePreferences = null;
        $visibleWidgets  = null;
        if (!empty($pageContext)) {
            $preferencesManager = new UserPreferencesManager();
            $pagePreferences    = $preferencesManager->get($pageContext);
            // If user has saved visible widgets preference, use it to filter queries
            if (!empty($pagePreferences['visibleWidgets'])) {
                $visibleWidgets = $pagePreferences['visibleWidgets'];
            }
        }

        foreach ($queries as $queryData) {
            // Query must have an ID
            $queryId = $queryData['id'] ?? null;
            if (empty($queryId)) {
                continue;
            }

            // Skip queries for hidden widgets (but never skip preference queries)
            // Queries ending with '_prefs' are always executed as they fetch preferences
            $isPrefsQuery = substr($queryId, -6) === '_prefs';
            if ($visibleWidgets !== null && !$isPrefsQuery) {
                if (!in_array($queryId, $visibleWidgets, true)) {
                    $skippedQueries[] = $queryId;
                    continue;
                }
            }

            // Apply global dates if query doesn't override both
            $hasCustomDates = isset($queryData['date_from']) && isset($queryData['date_to']);
            if (!$hasCustomDates) {
                if ($dateFrom !== null) {
                    $queryData['date_from'] = $dateFrom;
                }
                if ($dateTo !== null) {
                    $queryData['date_to'] = $dateTo;
                }
            }

            // Apply global previous dates for comparison if query doesn't override both
            $hasCustomPreviousDates = isset($queryData['previous_date_from']) && isset($queryData['previous_date_to']);
            if (!$hasCustomPreviousDates) {
                if ($globalPreviousDateFrom !== null) {
                    $queryData['previous_date_from'] = $globalPreviousDateFrom;
                }
                if ($globalPreviousDateTo !== null) {
                    $queryData['previous_date_to'] = $globalPreviousDateTo;
                }
            }

            // Normalize and merge global filters with query filters (query values override)
            // Skip filters for preference queries (they only fetch metadata, not actual data)
            if (!$isPrefsQuery) {
                $normalizedGlobalFilters = $this->normalizeFilters($globalFilters);
                $normalizedQueryFilters  = $this->normalizeFilters($queryData['filters'] ?? []);

                if (!empty($normalizedGlobalFilters) || !empty($normalizedQueryFilters)) {
                    $queryData['filters'] = array_merge($normalizedGlobalFilters, $normalizedQueryFilters);
                }
            }

            // Apply global compare if query doesn't override
            if (!isset($queryData['compare']) && $globalCompare) {
                $queryData['compare'] = true;
            }

            try {
                $result            = $this->handle($queryData);
                $results[$queryId] = $result;
            } catch (\Exception $e) {
                $errors[$queryId] = [
                    'code'    => $this->getErrorCode($e),
                    'message' => $e->getMessage(),
                ];
            }
        }

        $response = [
            'success' => empty($errors) || !empty($results),
            'items'   => $results,
            'errors'  => $errors,
        ];

        // Include skipped queries in the response for debugging/transparency
        if (!empty($skippedQueries)) {
            $response['skipped'] = $skippedQueries;
        }

        // Include page preferences in meta for the frontend to use
        if ($pagePreferences !== null) {
            $response['meta'] = [
                'preferences' => $pagePreferences,
            ];
        }

        return $response;
    }

    /**
     * Validate a query request.
     *
     * @param array $request Query request.
     * @throws InvalidSourceException
     * @throws InvalidGroupByException
     * @throws InvalidDateRangeException
     */
    private function validate(array $request): void
    {
        // Validate sources
        $sources = $request['sources'] ?? [];
        if (empty($sources)) {
            throw new \InvalidArgumentException(
                __('At least one source is required.', 'wp-statistics')
            );
        }

        foreach ($sources as $source) {
            if (!$this->sourceRegistry->has($source)) {
                throw new InvalidSourceException($source);
            }
        }

        // Validate group by
        $groupBy = $request['group_by'] ?? [];
        foreach ($groupBy as $groupByItem) {
            if (!$this->groupByRegistry->has($groupByItem)) {
                throw new InvalidGroupByException($groupByItem);
            }
        }

        // Validate format
        if (isset($request['format'])) {
            $validFormats = ['table', 'flat', 'chart', 'export'];
            if (!in_array($request['format'], $validFormats, true)) {
                throw new InvalidFormatException($request['format']);
            }
        }

        // Validate columns if provided
        if (!empty($request['columns'])) {
            $this->validateColumns($request['columns'], $sources, $groupBy);
        }

        // Validate date range format
        $dateFrom = $request['date_from'] ?? null;
        $dateTo   = $request['date_to'] ?? null;

        if ($dateFrom && !$this->isValidDate($dateFrom)) {
            throw new InvalidDateRangeException(
                sprintf(
                    __('Invalid date_from format: %s. Expected YYYY-MM-DD, YYYY-MM-DD HH:mm:ss, YYYY-MM-DDTHH:mm:ss, or YYYY-MM-DD HH:mm:ssZ.', 'wp-statistics'),
                    $dateFrom
                )
            );
        }

        if ($dateTo && !$this->isValidDate($dateTo)) {
            throw new InvalidDateRangeException(
                sprintf(
                    __('Invalid date_to format: %s. Expected YYYY-MM-DD, YYYY-MM-DD HH:mm:ss, YYYY-MM-DDTHH:mm:ss, or YYYY-MM-DD HH:mm:ssZ.', 'wp-statistics'),
                    $dateTo
                )
            );
        }

        // Validate date range logic (compare dates only, not times)
        if ($dateFrom && $dateTo) {
            $fromDate = substr($dateFrom, 0, 10);
            $toDate   = substr($dateTo, 0, 10);
            if ($fromDate > $toDate) {
                throw new InvalidDateRangeException(
                    __('date_from cannot be after date_to.', 'wp-statistics')
                );
            }
        }
    }

    /**
     * Check if a date/time string is valid.
     *
     * Supported formats:
     * - Date only: YYYY-MM-DD
     * - With space: YYYY-MM-DD HH:mm:ss (24-hour format)
     * - ISO 8601: YYYY-MM-DDTHH:mm:ss (JavaScript-friendly with T separator)
     * - UTC format: YYYY-MM-DD HH:mm:ssZ or YYYY-MM-DDTHH:mm:ssZ
     * - With timezone: YYYY-MM-DD HH:mm:ss+00:00 or YYYY-MM-DDTHH:mm:ss+00:00
     *
     * @param string $date Date/time string.
     * @return bool
     */
    private function isValidDate(string $date): bool
    {
        // Try date only format: YYYY-MM-DD
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if ($d && $d->format('Y-m-d') === $date) {
            return true;
        }

        // Try datetime with space: YYYY-MM-DD HH:mm:ss
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        if ($d && $d->format('Y-m-d H:i:s') === $date) {
            return true;
        }

        // Try ISO 8601 format: YYYY-MM-DDTHH:mm:ss
        $d = \DateTime::createFromFormat('Y-m-d\TH:i:s', $date);
        if ($d && $d->format('Y-m-d\TH:i:s') === $date) {
            return true;
        }

        // Try UTC format with Z: YYYY-MM-DD HH:mm:ssZ
        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}Z$/', $date)) {
            return true;
        }

        // Try ISO 8601 UTC format with Z: YYYY-MM-DDTHH:mm:ssZ
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $date)) {
            return true;
        }

        // Try format with timezone offset: YYYY-MM-DD HH:mm:ss+00:00 or YYYY-MM-DDTHH:mm:ss+00:00
        if (preg_match('/^\d{4}-\d{2}-\d{2}[\sT]\d{2}:\d{2}:\d{2}[\+\-]\d{2}:\d{2}$/', $date)) {
            return true;
        }

        return false;
    }

    /**
     * Normalize request with default values.
     *
     * @param array $request Query request.
     * @return array Normalized request.
     */
    private function normalizeRequest(array $request): array
    {
        // Default date range: last 30 days
        if (empty($request['date_from'])) {
            $request['date_from'] = date('Y-m-d 00:00:00', strtotime('-30 days'));
        } else {
            $request['date_from'] = $this->normalizeDateFrom($request['date_from']);
        }

        if (empty($request['date_to'])) {
            $request['date_to'] = date('Y-m-d 23:59:59');
        } else {
            $request['date_to'] = $this->normalizeDateTo($request['date_to']);
        }

        // Normalize custom previous period dates if provided
        if (!empty($request['previous_date_from'])) {
            $request['previous_date_from'] = $this->normalizeDateFrom($request['previous_date_from']);
        }

        if (!empty($request['previous_date_to'])) {
            $request['previous_date_to'] = $this->normalizeDateTo($request['previous_date_to']);
        }

        // Default pagination
        if (!isset($request['page'])) {
            $request['page'] = 1;
        }

        if (!isset($request['per_page'])) {
            // For chart format with time-series groupBy (date, week, month), use higher limit
            // to ensure all dates are included without pagination
            $timeSeriesGroupBy = ['date', 'week', 'month'];
            $groupBy = $request['group_by'] ?? [];
            $format = $request['format'] ?? 'table';
            $isTimeSeriesChart = $format === 'chart' &&
                                 !empty($groupBy) &&
                                 count($groupBy) === 1 &&
                                 in_array($groupBy[0], $timeSeriesGroupBy, true);

            $request['per_page'] = $isTimeSeriesChart ? 1000 : 10;
        }

        // Ensure arrays
        if (!isset($request['sources'])) {
            $request['sources'] = [];
        }

        if (!isset($request['group_by'])) {
            $request['group_by'] = [];
        }

        if (!isset($request['filters'])) {
            $request['filters'] = [];
        }

        // Normalize filters from frontend array format to key-value format
        $request['filters'] = $this->normalizeFilters($request['filters']);

        // When aggregate_others is enabled, we need to fetch more rows to aggregate
        // Store the original per_page for final output, but fetch more initially
        if (!empty($request['aggregate_others'])) {
            $request['_original_per_page'] = $request['per_page'];
            $request['per_page'] = 1000; // Fetch enough rows to aggregate
        }

        return $request;
    }

    /**
     * Normalize date_from value.
     *
     * If only date is provided (YYYY-MM-DD), adds default time 00:00:00.
     * ISO 8601 format (with T) is converted to space format.
     * Preserves UTC indicator 'Z' or timezone offset for proper conversion.
     *
     * @param string $date Date/time string.
     * @return string Normalized datetime (YYYY-MM-DD HH:mm:ss or YYYY-MM-DD HH:mm:ssZ).
     */
    private function normalizeDateFrom(string $date): string
    {
        // Preserve UTC indicator or timezone offset
        $hasUtcIndicator = preg_match('/(Z|[\+\-]\d{2}:\d{2})$/', $date, $matches);
        $utcSuffix = $hasUtcIndicator ? $matches[1] : '';

        // Remove UTC indicator temporarily for processing
        if ($hasUtcIndicator) {
            $date = preg_replace('/(Z|[\+\-]\d{2}:\d{2})$/', '', $date);
        }

        // Replace T with space for ISO 8601 format
        $date = str_replace('T', ' ', $date);

        // If only date provided, add default start time
        if (strlen($date) === 10) {
            return $date . ' 00:00:00' . $utcSuffix;
        }

        return $date . $utcSuffix;
    }

    /**
     * Normalize date_to value.
     *
     * If only date is provided (YYYY-MM-DD), adds default time 23:59:59.
     * ISO 8601 format (with T) is converted to space format.
     * Preserves UTC indicator 'Z' or timezone offset for proper conversion.
     *
     * @param string $date Date/time string.
     * @return string Normalized datetime (YYYY-MM-DD HH:mm:ss or YYYY-MM-DD HH:mm:ssZ).
     */
    private function normalizeDateTo(string $date): string
    {
        // Preserve UTC indicator or timezone offset
        $hasUtcIndicator = preg_match('/(Z|[\+\-]\d{2}:\d{2})$/', $date, $matches);
        $utcSuffix = $hasUtcIndicator ? $matches[1] : '';

        // Remove UTC indicator temporarily for processing
        if ($hasUtcIndicator) {
            $date = preg_replace('/(Z|[\+\-]\d{2}:\d{2})$/', '', $date);
        }

        // Replace T with space for ISO 8601 format
        $date = str_replace('T', ' ', $date);

        // If only date provided, add default end time
        if (strlen($date) === 10) {
            return $date . ' 23:59:59' . $utcSuffix;
        }

        return $date . $utcSuffix;
    }

    /**
     * Normalize filters from frontend array format to backend key-value format.
     *
     * Converts [{key, operator, value}] to {key: value} or {key: {operator: value}}.
     *
     * @param array $filters Filters in frontend or backend format.
     * @return array Filters in backend key-value format.
     */
    private function normalizeFilters(array $filters): array
    {
        // If empty or already in key-value format, return as-is
        if (empty($filters) || !isset($filters[0])) {
            return $filters;
        }

        // Operator mapping from frontend to backend
        $operatorMap = [
            'equal'                  => 'is',
            'not_equal'              => 'is_not',
            'greater_than'           => 'gt',
            'greater_than_or_equal'  => 'gte',
            'less_than'              => 'lt',
            'less_than_or_equal'     => 'lte',
            // Keep existing operators as-is
            'is'                     => 'is',
            'is_not'                 => 'is_not',
            'in'                     => 'in',
            'not_in'                 => 'not_in',
            'contains'               => 'contains',
            'starts_with'            => 'starts_with',
            'ends_with'              => 'ends_with',
            'gt'                     => 'gt',
            'gte'                    => 'gte',
            'lt'                     => 'lt',
            'lte'                    => 'lte',
        ];

        $normalized = [];

        foreach ($filters as $filter) {
            $key      = $filter['key'] ?? null;
            $operator = $filter['operator'] ?? 'equal';
            $value    = $filter['value'] ?? null;

            if ($key === null) {
                continue;
            }

            $mappedOperator = $operatorMap[$operator] ?? $operator;

            // Simple equality can be stored directly as value
            if ($mappedOperator === 'is') {
                $normalized[$key] = $value;
            } else {
                $normalized[$key] = [$mappedOperator => $value];
            }
        }

        return $normalized;
    }

    /**
     * Validate columns against available sources and group_by fields.
     *
     * @param array $columns  Columns to validate.
     * @param array $sources  Valid sources.
     * @param array $groupBy  Valid group by fields.
     * @throws InvalidColumnException
     */
    private function validateColumns(array $columns, array $sources, array $groupBy): void
    {
        // Build list of valid column WP_Statistics_names (sources + group_by aliases + extra column aliases + post-processed columns)
        $validColumns = $sources;

        foreach ($groupBy as $groupByName) {
            $groupByObj = $this->groupByRegistry->get($groupByName);
            if ($groupByObj) {
                $validColumns[] = $groupByObj->getAlias();
                // Also include extra column aliases (e.g., country_code from country group_by)
                $validColumns = array_merge($validColumns, $groupByObj->getExtraColumnAliases());
                // Include post-processed columns (e.g., comments, thumbnail_url from page group_by)
                $validColumns = array_merge($validColumns, $groupByObj->getPostProcessedColumns());
            } else {
                $validColumns[] = $groupByName;
            }
        }

        // Check each requested column
        foreach ($columns as $column) {
            if (!in_array($column, $validColumns, true)) {
                throw new InvalidColumnException($column);
            }
        }
    }

    /**
     * Filter result data to only include specified columns.
     *
     * @param array $result  Query result with rows, totals, etc.
     * @param Query $query   Query object.
     * @return array Filtered result.
     */
    private function filterColumns(array $result, Query $query): array
    {
        if (!$query->hasColumns()) {
            return $result;
        }

        $columns = $query->getColumns();

        // Filter rows
        if (!empty($result['rows'])) {
            $result['rows'] = $this->filterRowColumns($result['rows'], $columns);
        }

        // Filter totals
        if (isset($result['totals']) && $result['totals'] !== null) {
            $result['totals'] = $this->filterTotalsColumns($result['totals'], $columns, $query);
        }

        return $result;
    }

    /**
     * Filter rows to only include specified columns in the specified order.
     *
     * @param array $rows    Result rows.
     * @param array $columns Columns to include.
     * @return array Filtered rows with columns in the specified order.
     */
    private function filterRowColumns(array $rows, array $columns): array
    {
        $filteredRows = [];

        foreach ($rows as $row) {
            $filteredRow = [];

            // Add columns in the order specified
            foreach ($columns as $column) {
                if (array_key_exists($column, $row)) {
                    $filteredRow[$column] = $row[$column];
                }
            }

            // Handle comparison data if present
            if (isset($row['previous'])) {
                $filteredPrevious = [];
                foreach ($columns as $column) {
                    // Only include source columns in previous (not group_by)
                    if (array_key_exists($column, $row['previous'])) {
                        $filteredPrevious[$column] = $row['previous'][$column];
                    }
                }
                if (!empty($filteredPrevious)) {
                    $filteredRow['previous'] = $filteredPrevious;
                }
            }

            // Preserve is_other flag if present
            if (isset($row['is_other'])) {
                $filteredRow['is_other'] = $row['is_other'];
            }

            $filteredRows[] = $filteredRow;
        }

        return $filteredRows;
    }

    /**
     * Filter totals to only include specified columns.
     *
     * @param array $totals  Totals data.
     * @param array $columns Columns to include.
     * @param Query $query   Query object.
     * @return array Filtered totals.
     */
    private function filterTotalsColumns(array $totals, array $columns, Query $query): array
    {
        $sources = $query->getSources();
        $filteredTotals = [];

        foreach ($columns as $column) {
            // Only include source columns in totals (group_by columns don't appear in totals)
            if (in_array($column, $sources, true) && array_key_exists($column, $totals)) {
                $filteredTotals[$column] = $totals[$column];
            }
        }

        // Handle comparison data in totals if present
        if (isset($totals['previous'])) {
            $filteredPrevious = [];
            foreach ($columns as $column) {
                if (in_array($column, $sources, true) && array_key_exists($column, $totals['previous'])) {
                    $filteredPrevious[$column] = $totals['previous'][$column];
                }
            }
            if (!empty($filteredPrevious)) {
                $filteredTotals['previous'] = $filteredPrevious;
            }
        }

        return $filteredTotals;
    }

    /**
     * Execute the analytics query.
     *
     * @param Query $query Query object.
     * @return array Query results.
     */
    private function executeQuery(Query $query): array
    {
        return $this->executeQueryDirect($query);
    }

    /**
     * Execute the analytics query directly from raw tables.
     *
     * @param Query $query Query object.
     * @return array Query results.
     */
    private function executeQueryDirect(Query $query): array
    {
        $groupBy    = $query->getGroupBy();
        $sources    = $query->getSources();
        $showTotals = $query->showTotals();

        // Execute main query
        $result = $this->executor->execute($query);

        // Only calculate totals if requested
        $totals = null;
        if ($showTotals) {
            // If group by are present and no rows returned, totals should be empty
            if (!empty($groupBy) && empty($result['rows'])) {
                $totals = [];
                foreach ($sources as $source) {
                    $totals[$source] = 0;
                }
            } else {
                // Try to calculate totals from rows when possible (no separate query needed)
                $canCalculateFromRows = $this->canCalculateTotalsFromRows($query, $result);

                if ($canCalculateFromRows) {
                    $totals = $this->calculateTotalsFromRows($result['rows'], $sources);
                } else {
                    // Execute separate totals query
                    $totals = $this->executor->executeTotals($query);
                }
            }
        }

        return [
            'rows'   => $result['rows'],
            'totals' => $totals,
            'total'  => $result['total'],
        ];
    }

    /**
     * Check if totals can be calculated from rows instead of a separate query.
     *
     * @param Query $query  Query object.
     * @param array $result Query result with rows.
     * @return bool True if totals can be calculated from rows.
     */
    private function canCalculateTotalsFromRows(Query $query, array $result): bool
    {
        // No GROUP BY means single row result, can use it directly
        if (empty($query->getGroupBy())) {
            return true;
        }

        // If we have all rows (no pagination or fetched all), we can sum them
        $rows = $result['rows'];
        $total = $result['total'];

        // We have all the data if row count equals total count
        return count($rows) >= $total;
    }

    /**
     * Calculate totals from query rows.
     *
     * Sums up source values from all rows to get totals.
     *
     * @param array $rows    Query result rows.
     * @param array $sources Source WP_Statistics_names.
     * @return array Totals array with source WP_Statistics_names as keys.
     */
    private function calculateTotalsFromRows(array $rows, array $sources): array
    {
        $totals = [];

        // Initialize totals
        foreach ($sources as $source) {
            $totals[$source] = 0;
        }

        // Sum up values from all rows
        foreach ($rows as $row) {
            foreach ($sources as $source) {
                if (isset($row[$source])) {
                    $totals[$source] += (float) $row[$source];
                }
            }
        }

        // Round totals to appropriate precision
        foreach ($sources as $source) {
            $sourceObj = $this->sourceRegistry->get($source);
            if ($sourceObj && $sourceObj->getType() === 'integer') {
                $totals[$source] = (int) round($totals[$source]);
            } else {
                $totals[$source] = round($totals[$source], 1);
            }
        }

        return $totals;
    }

    /**
     * Add comparison data to results.
     *
     * @param Query $query  Query object.
     * @param array $result Query results.
     * @return array Results with comparison.
     */
    private function addComparison(Query $query, array $result): array
    {
        $sources    = $query->getSources();
        $groupBy    = $query->getGroupBy();
        $showTotals = $query->showTotals();

        // Use custom previous period if provided, otherwise calculate automatically
        if ($query->hasCustomPreviousPeriod()) {
            $previousPeriod = [
                'from' => $query->getPreviousDateFrom(),
                'to'   => $query->getPreviousDateTo(),
            ];
        } else {
            // Calculate previous period automatically
            $previousPeriod = $this->comparisonHandler->calculatePreviousPeriod(
                $query->getDateFrom(),
                $query->getDateTo()
            );
        }

        // Create query for previous period
        $prevQuery = $query->withDateRange($previousPeriod['from'], $previousPeriod['to'])
                          ->withoutComparison();

        // Execute previous period query
        $prevResult = $this->executeQuery($prevQuery);

        // Set up comparison handler
        $this->comparisonHandler
            ->setSources($sources)
            ->setGroupBy($groupBy);

        // Merge comparison into rows
        if (!empty($groupBy)) {
            $result['rows'] = $this->comparisonHandler->mergeResults(
                $result['rows'],
                $prevResult['rows']
            );
        }

        // Merge comparison into totals (only if totals are requested)
        if ($showTotals && $result['totals'] !== null) {
            $result['totals'] = $this->comparisonHandler->mergeTotals(
                $result['totals'],
                $prevResult['totals']
            );
        }

        // Add comparison period info
        $result['compare_from'] = $previousPeriod['from'];
        $result['compare_to']   = $previousPeriod['to'];

        return $result;
    }

    /**
     * Build the final response structure.
     *
     * Delegates to the appropriate formatter based on the query's format setting.
     *
     * @param Query $query  Query object.
     * @param array $result Query results.
     * @return array Response structure.
     */
    private function buildResponse(Query $query, array $result): array
    {
        $groupBy = $query->getGroupBy();
        $rows    = $result['rows'] ?? [];

        // Handle aggregate_others: show top N-1 items + "Other" row
        // This is applied before formatting since it modifies the data structure
        if ($query->hasAggregateOthers() && !empty($groupBy) && count($rows) > 0) {
            $result['rows'] = $this->aggregateOthersRows($rows, $query);
        }

        // Apply column filtering if columns parameter is specified
        // This filters both rows and totals to only include specified columns
        // and reorders columns according to the columns array order
        $result = $this->filterColumns($result, $query);

        // Get the appropriate formatter and format the response
        $formatter = $this->getFormatter($query->getFormat());

        return $formatter->format($query, $result);
    }

    /**
     * Aggregate rows beyond top N into an "Other" row.
     *
     * When aggregate_others is true, shows per_page - 1 items with data
     * and aggregates all remaining rows into a single "Other" row.
     *
     * @param array $rows  Result rows.
     * @param Query $query Query object.
     * @return array Rows with "Other" aggregation.
     */
    private function aggregateOthersRows(array $rows, Query $query): array
    {
        $limit   = $query->getAggregationLimit(); // Use original per_page as the final output count
        $sources = $query->getSources();
        $groupBy = $query->getGroupBy();

        // If we have fewer or equal rows than the limit, no aggregation needed
        if (count($rows) <= $limit) {
            return $rows;
        }

        // Take first N-1 rows
        $topRows    = array_slice($rows, 0, $limit - 1);
        $otherRows  = array_slice($rows, $limit - 1);

        // Build "Other" row by summing source values
        $otherRow = [];

        // Set group by field(s) to "Other"
        foreach ($groupBy as $groupByItem) {
            $groupByObj = $this->groupByRegistry->get($groupByItem);
            $alias      = $groupByObj ? $groupByObj->getAlias() : $groupByItem;
            $otherRow[$alias] = __('Other', 'wp-statistics');
        }

        // Sum up source values
        foreach ($sources as $source) {
            $otherRow[$source] = 0;
            foreach ($otherRows as $row) {
                $otherRow[$source] += (float) ($row[$source] ?? 0);
            }
        }

        // Handle comparison data if present
        if (isset($otherRows[0]['previous'])) {
            $otherRow['previous'] = [];
            foreach ($sources as $source) {
                $otherRow['previous'][$source] = 0;
                foreach ($otherRows as $row) {
                    $otherRow['previous'][$source] += (float) ($row['previous'][$source] ?? 0);
                }
            }
        }

        // Mark as aggregated
        $otherRow['is_other'] = true;

        $topRows[] = $otherRow;

        return $topRows;
    }

    /**
     * Get error code from exception.
     *
     * @param \Exception $e Exception.
     * @return string Error code.
     */
    private function getErrorCode(\Exception $e): string
    {
        if ($e instanceof InvalidSourceException) {
            return 'invalid_source';
        }

        if ($e instanceof InvalidGroupByException) {
            return 'invalid_group_by';
        }

        if ($e instanceof InvalidDateRangeException) {
            return 'invalid_date_range';
        }

        if ($e instanceof InvalidFormatException) {
            return 'invalid_format';
        }

        if ($e instanceof InvalidColumnException) {
            return 'invalid_column';
        }

        return 'server_error';
    }

    /**
     * Clear all analytics cache.
     *
     * @return int Number of entries cleared.
     */
    public function clearCache(): int
    {
        return $this->cacheManager->clearAll();
    }

    /**
     * Enable or disable caching.
     *
     * @param bool $enabled Whether caching is enabled.
     * @return self
     */
    public function setCacheEnabled(bool $enabled): self
    {
        $this->cacheManager->setEnabled($enabled);
        return $this;
    }

    /**
     * Get available sources.
     *
     * @return array List of source WP_Statistics_names with their metadata.
     */
    public function getAvailableSources(): array
    {
        $sources = [];
        foreach ($this->sourceRegistry->getAll() as $name) {
            $source = $this->sourceRegistry->get($name);
            if ($source) {
                $sources[$name] = [
                    'type'   => $source->getType(),
                    'format' => $source->getFormat(),
                ];
            }
        }
        return $sources;
    }

    /**
     * Get available group by.
     *
     * @return array List of group by WP_Statistics_names.
     */
    public function getAvailableGroupBy(): array
    {
        return $this->groupByRegistry->getAll();
    }

    /**
     * Get the source registry.
     *
     * @return SourceRegistry
     */
    public function getSourceRegistry(): SourceRegistry
    {
        return $this->sourceRegistry;
    }

    /**
     * Get the group by registry.
     *
     * @return GroupByRegistry
     */
    public function getGroupByRegistry(): GroupByRegistry
    {
        return $this->groupByRegistry;
    }

    /**
     * Get available response formats.
     *
     * @return array List of available format WP_Statistics_names.
     */
    public function getAvailableFormats(): array
    {
        return array_keys($this->formatters);
    }

    /**
     * Apply user preferences to request before execution.
     *
     * Loads saved user preferences for the given context and applies them to the request.
     * Currently supports:
     * - columns: Filters which columns to include in the response
     *
     * @param array  $request Request array.
     * @param string $context Context identifier for preferences lookup.
     * @return array Request with preferences applied.
     */
    private function applyUserPreferences(array $request, string $context): array
    {
        $preferencesManager = new UserPreferencesManager();
        $preferences = $preferencesManager->get($context);

        if (empty($preferences)) {
            return $request;
        }

        // Apply columns preference if not already specified in request
        if (!empty($preferences['columns']) && !isset($request['columns'])) {
            $request['columns'] = $preferences['columns'];
        }

        return $request;
    }

    /**
     * Add user preferences to response metadata.
     *
     * If a context is provided, looks up user preferences for that context
     * and adds them to the response meta.preferences field.
     *
     * @param array       $response Response array from formatter.
     * @param string|null $context  Context identifier for preferences lookup.
     * @return array Response with preferences added to meta.
     */
    private function addUserPreferences(array $response, ?string $context): array
    {
        // Always add preferences key to meta, even if null
        if (!isset($response['meta'])) {
            $response['meta'] = [];
        }

        // If no context provided, preferences is null
        if (empty($context)) {
            $response['meta']['preferences'] = null;
            return $response;
        }

        // Look up user preferences for this context
        $preferencesManager = new UserPreferencesManager();
        $preferences = $preferencesManager->get($context);

        // Add preferences to meta (null if not found)
        $response['meta']['preferences'] = $preferences;

        return $response;
    }
}
