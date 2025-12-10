<?php

namespace WP_Statistics\Service\AnalyticsQuery\Query;

use WP_Statistics\Service\AnalyticsQuery\Contracts\QueryInterface;

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
     * Constructor.
     *
     * @param array       $sources    Sources to retrieve.
     * @param array       $groupBy    Group by to group by.
     * @param array       $filters    Filters to apply.
     * @param string|null $dateFrom   Start date.
     * @param string|null $dateTo     End date.
     * @param string|null $orderBy    ORDER BY field.
     * @param string      $order      ORDER direction.
     * @param int         $page       Page number.
     * @param int         $perPage    Items per page.
     * @param bool        $compare    Whether comparison is enabled.
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
        bool $compare = false
    ) {
        $this->sources    = $sources;
        $this->groupBy    = $groupBy;
        $this->filters    = $filters;
        $this->dateFrom   = $dateFrom;
        $this->dateTo     = $dateTo;
        $this->orderBy    = $orderBy;
        $this->order      = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $this->page       = max(1, $page);
        $this->perPage    = min(100, max(1, $perPage));
        $this->compare    = $compare;
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
     * Get the LIMIT offset.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'sources'    => $this->sources,
            'group_by'   => $this->groupBy,
            'filters'    => $this->filters,
            'date_from'  => $this->dateFrom,
            'date_to'    => $this->dateTo,
            'order_by'   => $this->orderBy,
            'order'      => $this->order,
            'page'       => $this->page,
            'per_page'   => $this->perPage,
            'compare'    => $this->compare,
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
            $this->compare
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
            false
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
        return new self(
            $data['sources'] ?? [],
            $data['group_by'] ?? [],
            $data['filters'] ?? [],
            $data['date_from'] ?? null,
            $data['date_to'] ?? null,
            $data['order_by'] ?? null,
            $data['order'] ?? 'DESC',
            $data['page'] ?? 1,
            $data['per_page'] ?? 10,
            $data['compare'] ?? false
        );
    }
}
