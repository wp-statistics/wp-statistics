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
     * Supported operators.
     *
     * @var array
     */
    protected $supportedOperators = [
        'is', 'is_not', 'in', 'not_in',
        'contains', 'starts_with', 'ends_with'
    ];

    /**
     * Input type for the filter UI.
     * Options: 'text', 'dropdown', 'searchable', 'multi-select', 'date', 'number', 'boolean'
     *
     * @var string
     */
    protected $inputType = 'text';

    /**
     * Static options for dropdown/multi-select filters.
     * Format: [['value' => 'key', 'label' => 'Label'], ...]
     *
     * @var array|null
     */
    protected $options = null;

    /**
     * Pages where this filter is available.
     * Example: ['visitors-overview', 'visitors', 'views']
     *
     * @var array
     */
    protected $pages = [];

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
     * Get the human-readable label for the filter.
     * Each filter must implement this with proper translation.
     *
     * @return string
     */
    abstract public function getLabel(): string;

    /**
     * Get the input type for UI rendering.
     *
     * @return string
     */
    public function getInputType(): string
    {
        return $this->inputType;
    }

    /**
     * Get static options for dropdown/multi-select filters.
     *
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * Get the pages where this filter is available.
     *
     * @return array
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * Check if this filter is searchable (requires AJAX).
     *
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->inputType === 'searchable';
    }

    /**
     * Get options for searchable filters via AJAX.
     * Override this method in subclasses that need searchable options.
     *
     * @param string $search Search term.
     * @param int    $limit  Maximum results.
     * @return array Array of options with 'value' and 'label'.
     */
    public function searchOptions(string $search = '', int $limit = 20): array
    {
        return [];
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
            'inputType'          => $this->getInputType(),
            'pages'              => $this->getPages(),
        ];

        if ($this->getJoins() !== null) {
            $data['joins'] = $this->getJoins();
        }

        if ($this->getRequirement() !== null) {
            $data['requirement'] = $this->getRequirement();
        }

        if ($this->getOptions() !== null) {
            $data['options'] = $this->getOptions();
        }

        return $data;
    }
}
