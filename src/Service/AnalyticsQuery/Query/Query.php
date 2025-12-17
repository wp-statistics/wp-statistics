<?php

namespace WP_Statistics\Service\AnalyticsQuery\Query;

use WP_Statistics\Service\AnalyticsQuery\Contracts\QueryInterface;
use WP_Statistics\Service\AnalyticsQuery\Exceptions\InvalidFormatException;

/**
 * Immutable query object.
 *
 * Represents a complete analytics query with all parameters.
 * Once created, the query cannot be modified.
 *
 * @since 15.0.0
 */
class Query implements QueryInterface
{
    /**
     * Sources to retrieve.
     *
     * @var array
     */
    private $sources;

    /**
     * Group by to group by.
     *
     * @var array
     */
    private $groupBy;

    /**
     * Filters to apply.
     *
     * @var array
     */
    private $filters;

    /**
     * Start date.
     *
     * @var string|null
     */
    private $dateFrom;

    /**
     * End date.
     *
     * @var string|null
     */
    private $dateTo;

    /**
     * ORDER BY field.
     *
     * @var string|null
     */
    private $orderBy;

    /**
     * ORDER direction.
     *
     * @var string
     */
    private $order;

    /**
     * Page number.
     *
     * @var int
     */
    private $page;

    /**
     * Items per page.
     *
     * @var int
     */
    private $perPage;

    /**
     * Whether comparison is enabled.
     *
     * @var bool
     */
    private $compare;

    /**
     * Custom date column for filtering.
     *
     * @var string|null
     */
    private $dateColumn;

    /**
     * Whether to aggregate remaining items as "Other".
     * When true, shows per_page - 1 items with data + 1 "Other" row.
     *
     * @var bool
     */
    private $aggregateOthers;

    /**
     * Original per_page value when aggregate_others is enabled.
     * Used to determine the final output count after aggregation.
     *
     * @var int|null
     */
    private $originalPerPage;

    /**
     * Whether to include totals in the response.
     *
     * @var bool
     */
    private $showTotals;

    /**
     * Response format type.
     *
     * Supported formats: 'table', 'flat', 'chart', 'export'.
     *
     * @var string
     */
    private $format;

    /**
     * Columns to include in the response.
     *
     * When set, only these columns are returned in the response.
     * Also defines the display order of columns.
     *
     * @var array|null
     */
    private $columns;

    /**
     * Whether a count query is needed for pagination.
     *
     * When false, skips the COUNT query to improve performance.
     * Defaults to true for backward compatibility.
     *
     * @var bool
     */
    private $needsCount;


    /**
     * Constructor.
     *
     * @param array       $sources         Sources to retrieve.
     * @param array       $groupBy         Group by to group by.
     * @param array       $filters         Filters to apply.
     * @param string|null $dateFrom        Start date.
     * @param string|null $dateTo          End date.
     * @param string|null $orderBy         ORDER BY field.
     * @param string      $order           ORDER direction.
     * @param int         $page            Page number.
     * @param int         $perPage         Items per page.
     * @param bool        $compare         Whether comparison is enabled.
     * @param string|null $dateColumn       Custom date column for filtering.
     * @param bool        $aggregateOthers  Whether to aggregate remaining items as "Other".
     * @param int|null    $originalPerPage  Original per_page when aggregate_others is enabled.
     * @param bool        $showTotals       Whether to include totals in the response.
     * @param string      $format           Response format type (table, flat, chart, export).
     * @param array|null  $columns          Columns to include in the response.
     * @param bool        $needsCount       Whether a count query is needed for pagination.
     */
    public function __construct(
        array $sources = [],
        array $groupBy = [],
        array $filters = [],
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $orderBy = null,
        string $order = 'DESC',
        int $page = 1,
        int $perPage = 10,
        bool $compare = false,
        ?string $dateColumn = null,
        bool $aggregateOthers = false,
        ?int $originalPerPage = null,
        bool $showTotals = true,
        string $format = 'table',
        ?array $columns = null,
        bool $needsCount = true
    ) {
        $this->sources          = $sources;
        $this->groupBy          = $groupBy;
        $this->filters          = $filters;
        $this->dateFrom         = $dateFrom;
        $this->dateTo           = $dateTo;
        $this->orderBy          = $orderBy;
        $this->order            = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $this->page             = max(1, $page);
        $this->perPage          = min(1000, max(1, $perPage)); // Allow up to 1000 for aggregation
        $this->compare          = $compare;
        $this->dateColumn       = $dateColumn;
        $this->aggregateOthers  = $aggregateOthers;
        $this->originalPerPage  = $originalPerPage;
        $this->showTotals       = $showTotals;
        $this->format           = $this->normalizeFormat($format);
        $this->columns          = $columns;
        $this->needsCount       = $needsCount;
    }

    /**
     * Normalize format value to a supported format.
     *
     * @param string $format Format value.
     * @return string Normalized format (defaults to 'table' if invalid).
     */
    private function normalizeFormat(string $format): string
    {
        $validFormats = ['table', 'flat', 'chart', 'export'];

        if (!in_array($format, $validFormats, true)) {
            throw new InvalidFormatException($format);
        }

        return $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupBy(): array
    {
        return $this->groupBy;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateFrom(): ?string
    {
        return $this->dateFrom;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateTo(): ?string
    {
        return $this->dateTo;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * {@inheritdoc}
     */
    public function hasComparison(): bool
    {
        return $this->compare;
    }

    /**
     * Get custom date column for filtering.
     *
     * @return string|null
     */
    public function getDateColumn(): ?string
    {
        return $this->dateColumn;
    }

    /**
     * Check if aggregation is enabled.
     *
     * @return bool
     */
    public function hasAggregateOthers(): bool
    {
        return $this->aggregateOthers;
    }

    /**
     * Get the original per_page value for aggregation output.
     *
     * @return int|null
     */
    public function getOriginalPerPage(): ?int
    {
        return $this->originalPerPage;
    }

    /**
     * Get the aggregation limit (final output count).
     *
     * Returns the original per_page if set, otherwise the current per_page.
     *
     * @return int
     */
    public function getAggregationLimit(): int
    {
        return $this->originalPerPage ?? $this->perPage;
    }

    /**
     * Get the LIMIT offset.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /**
     * Check if totals should be included in the response.
     *
     * @return bool
     */
    public function showTotals(): bool
    {
        return $this->showTotals;
    }

    /**
     * Get the response format type.
     *
     * @return string Format type (table, flat, chart, export).
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Get columns to include in the response.
     *
     * @return array|null Columns array, or null if all columns should be returned.
     */
    public function getColumns(): ?array
    {
        return $this->columns;
    }

    /**
     * Check if column filtering is enabled.
     *
     * @return bool
     */
    public function hasColumns(): bool
    {
        return $this->columns !== null && !empty($this->columns);
    }

    /**
     * Check if a count query is needed for pagination.
     *
     * @return bool
     */
    public function needsCount(): bool
    {
        return $this->needsCount;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'sources'            => $this->sources,
            'group_by'           => $this->groupBy,
            'filters'            => $this->filters,
            'date_from'          => $this->dateFrom,
            'date_to'            => $this->dateTo,
            'date_column'        => $this->dateColumn,
            'order_by'           => $this->orderBy,
            'order'              => $this->order,
            'page'               => $this->page,
            'per_page'           => $this->perPage,
            'compare'            => $this->compare,
            'aggregate_others'   => $this->aggregateOthers,
            '_original_per_page' => $this->originalPerPage,
            'show_totals'        => $this->showTotals,
            'format'             => $this->format,
            'columns'            => $this->columns,
            'needs_count'        => $this->needsCount,
        ];
    }

    /**
     * Create a new query with modified date range.
     *
     * @param string $dateFrom Start date.
     * @param string $dateTo   End date.
     * @return self
     */
    public function withDateRange(string $dateFrom, string $dateTo): self
    {
        return new self(
            $this->sources,
            $this->groupBy,
            $this->filters,
            $dateFrom,
            $dateTo,
            $this->orderBy,
            $this->order,
            $this->page,
            $this->perPage,
            $this->compare,
            $this->dateColumn,
            $this->aggregateOthers,
            $this->originalPerPage,
            $this->showTotals,
            $this->format,
            $this->columns,
            $this->needsCount
        );
    }

    /**
     * Create a new query with comparison disabled.
     *
     * @return self
     */
    public function withoutComparison(): self
    {
        return new self(
            $this->sources,
            $this->groupBy,
            $this->filters,
            $this->dateFrom,
            $this->dateTo,
            $this->orderBy,
            $this->order,
            $this->page,
            $this->perPage,
            false,
            $this->dateColumn,
            $this->aggregateOthers,
            $this->originalPerPage,
            $this->showTotals,  // Keep same showTotals setting
            $this->format,
            $this->columns,
            false  // Previous period queries don't need count
        );
    }

    /**
     * Create a Query from an array.
     *
     * @param array $data Query data.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        // Determine if count query is needed based on format and groupBy
        $needsCount = $data['needs_count'] ?? true;

        // Skip count for flat format queries (single value, no pagination)
        if (($data['format'] ?? 'table') === 'flat') {
            $needsCount = false;
        }

        // If no group_by, we don't need pagination (single row result)
        $groupBy = $data['group_by'] ?? [];
        $defaultPerPage = empty($groupBy) ? null : 10;

        return new self(
            $data['sources'] ?? [],
            $groupBy,
            $data['filters'] ?? [],
            $data['date_from'] ?? null,
            $data['date_to'] ?? null,
            $data['order_by'] ?? null,
            $data['order'] ?? 'DESC',
            $data['page'] ?? 1,
            $data['per_page'] ?? $defaultPerPage,
            $data['compare'] ?? false,
            $data['date_column'] ?? null,
            !empty($data['aggregate_others']),
            isset($data['_original_per_page']) ? (int) $data['_original_per_page'] : null,
            $data['show_totals'] ?? true,
            $data['format'] ?? 'table',
            $data['columns'] ?? null,
            $needsCount
        );
    }
}
