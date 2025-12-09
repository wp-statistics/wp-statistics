<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

use WP_Statistics\Service\AnalyticsQuery\Contracts\FilterInterface;

/**
 * Abstract base class for filters.
 *
 * @since 15.0.0
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * Filter name/identifier.
     *
     * @var string
     */
    protected $name;

    /**
     * SQL column expression.
     *
     * @var string
     */
    protected $column;

    /**
     * Data type for sanitization.
     *
     * @var string
     */
    protected $type = 'string';

    /**
     * JOIN configurations.
     *
     * @var array|null
     */
    protected $joins = null;

    /**
     * Table requirement.
     *
     * @var string|null
     */
    protected $requirement = null;

    /**
     * Human-readable label.
     *
     * @var string
     */
    protected $label;

    /**
     * Supported operators.
     *
     * @var array
     */
    protected $supportedOperators = [
        'is', 'is_not', 'in', 'not_in',
        'contains', 'starts_with', 'ends_with'
    ];

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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getJoins(): ?array
    {
        if ($this->joins === null) {
            return null;
        }

        // Normalize to array of joins
        if (isset($this->joins['table'])) {
            return [$this->joins];
        }

        return $this->joins;
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
     */
    public function getSupportedOperators(): array
    {
        return $this->supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return $this->label ?? ucfirst(str_replace('_', ' ', $this->name));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $data = [
            'name'               => $this->getName(),
            'column'             => $this->getColumn(),
            'type'               => $this->getType(),
            'label'              => $this->getLabel(),
            'supportedOperators' => $this->getSupportedOperators(),
        ];

        if ($this->getJoins() !== null) {
            $data['joins'] = $this->getJoins();
        }

        if ($this->getRequirement() !== null) {
            $data['requirement'] = $this->getRequirement();
        }

        return $data;
    }
}
