<?php

namespace WP_Statistics\Service\AnalyticsQuery;

use WP_Statistics\Service\AnalyticsQuery\Query\Query;
use WP_Statistics\Service\AnalyticsQuery\Query\QueryExecutor;
use WP_Statistics\Service\AnalyticsQuery\Registry\SourceRegistry;
use WP_Statistics\Service\AnalyticsQuery\Registry\GroupByRegistry;
use WP_Statistics\Service\AnalyticsQuery\Cache\CacheManager;
use WP_Statistics\Service\AnalyticsQuery\Comparison\ComparisonHandler;
use WP_Statistics\Service\AnalyticsQuery\Contracts\FormatterInterface;
use WP_Statistics\Service\AnalyticsQuery\Formatters\StandardFormatter;
use WP_Statistics\Service\AnalyticsQuery\Formatters\FlatFormatter;
use WP_Statistics\Service\AnalyticsQuery\Formatters\ChartFormatter;
use WP_Statistics\Service\AnalyticsQuery\Formatters\ExportFormatter;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidSourceException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidGroupByException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidDateRangeException;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidFormatException;

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
            new StandardFormatter($this->cacheManager),
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
        return $this->formatters[$format] ?? $this->formatters['standard'];
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

        // Cache the result
        $this->cacheManager->set($request, $response);

        return $response;
    }

    /**
     * Handle a batch of analytics queries.
     *
     * Supports global parameters that apply to all queries:
     * - date_from/date_to: Inherited unless query provides both
     * - filters: Merged with query-specific filters (query values override)
     * - compare: Applied to queries that don't explicitly set it
     *
     * @param array       $queries        Array of queries with 'id' field.
     * @param string|null $dateFrom       Global date_from (queries can override).
     * @param string|null $dateTo         Global date_to (queries can override).
     * @param array       $globalFilters  Global filters (merged with query filters).
     * @param bool        $globalCompare  Global compare flag (queries can override).
     * @return array Response with results keyed by query ID.
     */
    public function handleBatch(
        array $queries,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        array $globalFilters = [],
        bool $globalCompare = false
    ): array {
        $results = [];
        $errors  = [];

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

        foreach ($queries as $queryData) {
            // Query must have an ID
            $queryId = $queryData['id'] ?? null;
            if (empty($queryId)) {
                continue;
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

            // Normalize and merge global filters with query filters (query values override)
            $normalizedGlobalFilters = $this->normalizeFilters($globalFilters);
            $normalizedQueryFilters  = $this->normalizeFilters($queryData['filters'] ?? []);

            if (!empty($normalizedGlobalFilters) || !empty($normalizedQueryFilters)) {
                $queryData['filters'] = array_merge($normalizedGlobalFilters, $normalizedQueryFilters);
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

        return [
            'success' => empty($errors) || !empty($results),
            'items'   => $results,
            'errors'  => $errors,
        ];
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
            $validFormats = ['standard', 'flat', 'chart', 'export'];
            if (!in_array($request['format'], $validFormats, true)) {
                throw new InvalidFormatException($request['format']);
            }
        }

        // Validate date range format
        $dateFrom = $request['date_from'] ?? null;
        $dateTo   = $request['date_to'] ?? null;

        if ($dateFrom && !$this->isValidDate($dateFrom)) {
            throw new InvalidDateRangeException(
                sprintf(
                    __('Invalid date_from format: %s. Expected YYYY-MM-DD, YYYY-MM-DD HH:mm:ss, or YYYY-MM-DDTHH:mm:ss.', 'wp-statistics'),
                    $dateFrom
                )
            );
        }

        if ($dateTo && !$this->isValidDate($dateTo)) {
            throw new InvalidDateRangeException(
                sprintf(
                    __('Invalid date_to format: %s. Expected YYYY-MM-DD, YYYY-MM-DD HH:mm:ss, or YYYY-MM-DDTHH:mm:ss.', 'wp-statistics'),
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

        // Default pagination
        if (!isset($request['page'])) {
            $request['page'] = 1;
        }

        if (!isset($request['per_page'])) {
            $request['per_page'] = 10;
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
     *
     * @param string $date Date/time string.
     * @return string Normalized datetime (YYYY-MM-DD HH:mm:ss).
     */
    private function normalizeDateFrom(string $date): string
    {
        // Replace T with space for ISO 8601 format
        $date = str_replace('T', ' ', $date);

        // If only date provided, add default start time
        if (strlen($date) === 10) {
            return $date . ' 00:00:00';
        }

        return $date;
    }

    /**
     * Normalize date_to value.
     *
     * If only date is provided (YYYY-MM-DD), adds default time 23:59:59.
     * ISO 8601 format (with T) is converted to space format.
     *
     * @param string $date Date/time string.
     * @return string Normalized datetime (YYYY-MM-DD HH:mm:ss).
     */
    private function normalizeDateTo(string $date): string
    {
        // Replace T with space for ISO 8601 format
        $date = str_replace('T', ' ', $date);

        // If only date provided, add default end time
        if (strlen($date) === 10) {
            return $date . ' 23:59:59';
        }

        return $date;
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
     * Execute the analytics query.
     *
     * @param Query $query Query object.
     * @return array Query results.
     */
    private function executeQuery(Query $query): array
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
                // Get totals
                $totals = $this->executor->executeTotals($query);
            }
        }

        return [
            'rows'   => $result['rows'],
            'totals' => $totals,
            'total'  => $result['total'],
        ];
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

        // Calculate previous period
        $previousPeriod = $this->comparisonHandler->calculatePreviousPeriod(
            $query->getDateFrom(),
            $query->getDateTo()
        );

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
     * @return array List of source names with their metadata.
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
     * @return array List of group by names.
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
     * @return array List of available format names.
     */
    public function getAvailableFormats(): array
    {
        return array_keys($this->formatters);
    }
}
