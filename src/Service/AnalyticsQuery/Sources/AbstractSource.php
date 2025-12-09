<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

use WP_Statistics\Service\AnalyticsQuery\Contracts\SourceInterface;

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
}
