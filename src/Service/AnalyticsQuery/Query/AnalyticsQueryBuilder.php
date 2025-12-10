<?php

namespace WP_Statistics\Service\AnalyticsQuery\Query;

/**
 * Fluent builder for Query objects.
 *
 * @since 15.0.0
 */
class AnalyticsQueryBuilder
{
    /**
     * Sources to retrieve.
     *
     * @var array
     */
    private $sources = [];

    /**
     * Group by to group by.
     *
     * @var array
     */
    private $groupBy = [];

    /**
     * Filters to apply.
     *
     * @var array
     */
    private $filters = [];

    /**
     * Start date.
     *
     * @var string|null
     */
    private $dateFrom = null;

    /**
     * End date.
     *
     * @var string|null
     */
    private $dateTo = null;

    /**
     * ORDER BY field.
     *
     * @var string|null
     */
    private $orderBy = null;

    /**
     * ORDER direction.
     *
     * @var string
     */
    private $order = 'DESC';

    /**
     * Page number.
     *
     * @var int
     */
    private $page = 1;

    /**
     * Items per page.
     *
     * @var int
     */
    private $perPage = 10;

    /**
     * Whether comparison is enabled.
     *
     * @var bool
     */
    private $compare = false;

    /**
     * Create a new builder instance.
     *
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set sources.
     *
     * @param array|string $sources Sources to retrieve.
     * @return self
     */
    public function sources($sources): self
    {
        $this->sources = is_array($sources) ? $sources : [$sources];
        return $this;
    }

    /**
     * Add a source.
     *
     * @param string $source Source name.
     * @return self
     */
    public function addSource(string $source): self
    {
        $this->sources[] = $source;
        return $this;
    }

    /**
     * Set group by.
     *
     * @param array|string $groupBy Group by to group by.
     * @return self
     */
    public function groupBy($groupBy): self
    {
        $this->groupBy = is_array($groupBy) ? $groupBy : [$groupBy];
        return $this;
    }

    /**
     * Add a group by.
     *
     * @param string $groupByItem Group by name.
     * @return self
     */
    public function addGroupBy(string $groupByItem): self
    {
        $this->groupBy[] = $groupByItem;
        return $this;
    }

    /**
     * Set filters.
     *
     * @param array $filters Filters to apply.
     * @return self
     */
    public function filters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Add a filter.
     *
     * @param string $field    Field name.
     * @param mixed  $value    Filter value.
     * @param string $operator Operator (is, is_not, in, etc.).
     * @return self
     */
    public function addFilter(string $field, $value, string $operator = 'is'): self
    {
        $this->filters[$field] = [
            'operator' => $operator,
            'value'    => $value,
        ];
        return $this;
    }

    /**
     * Set date range.
     *
     * Supported formats:
     * - Date only: YYYY-MM-DD (defaults to 00:00:00 for $from, 23:59:59 for $to)
     * - With space: YYYY-MM-DD HH:mm:ss (24-hour format)
     * - ISO 8601: YYYY-MM-DDTHH:mm:ss (JavaScript-friendly with T separator)
     *
     * @param string $from Start date/time.
     * @param string $to   End date/time.
     * @return self
     */
    public function dateRange(string $from, string $to): self
    {
        $this->dateFrom = $from;
        $this->dateTo   = $to;
        return $this;
    }

    /**
     * Set start date.
     *
     * Supported formats:
     * - Date only: YYYY-MM-DD (defaults to 00:00:00)
     * - With space: YYYY-MM-DD HH:mm:ss (24-hour format)
     * - ISO 8601: YYYY-MM-DDTHH:mm:ss (JavaScript-friendly with T separator)
     *
     * @param string $date Start date/time.
     * @return self
     */
    public function from(string $date): self
    {
        $this->dateFrom = $date;
        return $this;
    }

    /**
     * Set end date.
     *
     * Supported formats:
     * - Date only: YYYY-MM-DD (defaults to 23:59:59)
     * - With space: YYYY-MM-DD HH:mm:ss (24-hour format)
     * - ISO 8601: YYYY-MM-DDTHH:mm:ss (JavaScript-friendly with T separator)
     *
     * @param string $date End date/time.
     * @return self
     */
    public function to(string $date): self
    {
        $this->dateTo = $date;
        return $this;
    }

    /**
     * Set last N days.
     *
     * @param int $days Number of days.
     * @return self
     */
    public function lastDays(int $days): self
    {
        $this->dateTo   = date('Y-m-d');
        $this->dateFrom = date('Y-m-d', strtotime("-{$days} days"));
        return $this;
    }

    /**
     * Set ORDER BY.
     *
     * @param string $field     Field name.
     * @param string $direction Direction (ASC or DESC).
     * @return self
     */
    public function orderBy(string $field, string $direction = 'DESC'): self
    {
        $this->orderBy = $field;
        $this->order   = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        return $this;
    }

    /**
     * Set pagination.
     *
     * @param int $page    Page number.
     * @param int $perPage Items per page.
     * @return self
     */
    public function paginate(int $page, int $perPage = 10): self
    {
        $this->page    = max(1, $page);
        $this->perPage = min(100, max(1, $perPage));
        return $this;
    }

    /**
     * Set items per page.
     *
     * @param int $perPage Items per page.
     * @return self
     */
    public function limit(int $perPage): self
    {
        $this->perPage = min(100, max(1, $perPage));
        return $this;
    }

    /**
     * Enable comparison.
     *
     * @param bool $compare Whether to enable comparison.
     * @return self
     */
    public function compare(bool $compare = true): self
    {
        $this->compare = $compare;
        return $this;
    }

    /**
     * Build the Query object.
     *
     * @return Query
     */
    public function build(): Query
    {
        return new Query(
            $this->sources,
            $this->groupBy,
            $this->filters,
            $this->dateFrom,
            $this->dateTo,
            $this->orderBy,
            $this->order,
            $this->page,
            $this->perPage,
            $this->compare
        );
    }

    /**
     * Reset the builder state.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->sources    = [];
        $this->groupBy    = [];
        $this->filters    = [];
        $this->dateFrom   = null;
        $this->dateTo     = null;
        $this->orderBy    = null;
        $this->order      = 'DESC';
        $this->page       = 1;
        $this->perPage    = 10;
        $this->compare    = false;

        return $this;
    }
}
