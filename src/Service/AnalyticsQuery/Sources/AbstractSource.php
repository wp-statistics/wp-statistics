<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

use WP_Statistics\Service\AnalyticsQuery\Contracts\SourceInterface;
use WP_Statistics\Service\AnalyticsQuery\Helpers\PublishedContentHelper;

/**
 * Abstract base class for sources.
 *
 * @since 15.0.0
 */
abstract class AbstractSource implements SourceInterface
{
    /**
     * Source name.
     *
     * @var string
     */
    protected $name;

    /**
     * SQL aggregation expression.
     *
     * @var string
     */
    protected $expression;

    /**
     * Primary table required.
     *
     * @var string
     */
    protected $table = 'sessions';

    /**
     * Data type.
     *
     * @var string
     */
    protected $type = 'integer';

    /**
     * Format hint.
     *
     * @var string
     */
    protected $format = 'number';

    /**
     * Special requirement.
     *
     * @var string|null
     */
    protected $requirement = null;

    /**
     * Query context (group_by dimension names).
     *
     * @var array
     */
    protected $context = [];

    /**
     * Active filters with their values.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Query start date (Y-m-d format).
     *
     * @var string|null
     */
    protected $dateFrom = null;

    /**
     * Query end date (Y-m-d format).
     *
     * @var string|null
     */
    protected $dateTo = null;

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
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpressionWithAlias(): string
    {
        return $this->expression . ' AS ' . $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequirement(): ?string
    {
        return $this->requirement;
    }

    /**
     * {@inheritdoc}
     *
     * Default implementation returns false for safety.
     * Only sources that can use summary tables (visitors, views, sessions, etc.)
     * should override this method to return true.
     */
    public function supportsSummaryTable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(array $groupBy, array $filters = [], ?string $dateFrom = null, ?string $dateTo = null): void
    {
        $this->context = $groupBy;
        $this->filters = $filters;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    /**
     * Check if context includes a dimension.
     *
     * @param string $dimension The dimension name to check.
     * @return bool
     */
    protected function hasContextDimension(string $dimension): bool
    {
        return in_array($dimension, $this->context, true);
    }

    /**
     * Get post_type SQL clause based on filters.
     *
     * If post_type filter is set, uses that value.
     * Otherwise, uses all public queryable types from WordPress.
     *
     * @param string $column The column to filter (e.g., 'p.post_type')
     * @return string SQL clause like "p.post_type IN ('post', 'page', 'product')"
     */
    protected function getPostTypeClause(string $column): string
    {
        return PublishedContentHelper::getPostTypeClause($column, $this->filters);
    }
}
