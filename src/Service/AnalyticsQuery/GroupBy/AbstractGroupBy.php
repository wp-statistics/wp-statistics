<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

use WP_Statistics\Service\AnalyticsQuery\Contracts\GroupByInterface;

/**
 * Abstract base class for group by.
 *
 * @since 15.0.0
 */
abstract class AbstractGroupBy implements GroupByInterface
{
    /**
     * Group by name.
     *
     * @var string
     */
    protected $name;

    /**
     * Primary column expression.
     *
     * @var string
     */
    protected $column;

    /**
     * Column alias.
     *
     * @var string
     */
    protected $alias;

    /**
     * Extra columns to include.
     *
     * @var array
     */
    protected $extraColumns = [];

    /**
     * JOIN configurations.
     *
     * @var array
     */
    protected $joins = [];

    /**
     * GROUP BY expression.
     *
     * @var string|null
     */
    protected $groupBy = null;

    /**
     * Default ORDER direction.
     *
     * @var string
     */
    protected $order = 'DESC';

    /**
     * WHERE filter.
     *
     * @var string|null
     */
    protected $filter = null;

    /**
     * Table requirement.
     *
     * @var string|null
     */
    protected $requirement = null;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectColumns(): array
    {
        $columns = [$this->column . ' AS ' . $this->alias];

        return array_merge($columns, $this->extraColumns);
    }

    /**
     * {@inheritdoc}
     */
    public function getJoins(): array
    {
        // Normalize to array of joins
        if (!empty($this->joins) && isset($this->joins['table'])) {
            return [$this->joins];
        }

        return $this->joins;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupBy(): ?string
    {
        return $this->groupBy;
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
    public function getFilter(): ?string
    {
        return $this->filter;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequirement(): ?string
    {
        return $this->requirement;
    }
}
