<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

use WP_Statistics\Service\AnalyticsQuery\Contracts\FilterInterface;

/**
 * Abstract base class for Analytics Query filters.
 *
 * Filters allow users to narrow down analytics data by specific criteria.
 * Each filter defines:
 * - What database column to filter on
 * - What operators are supported (equals, contains, etc.)
 * - How the filter appears in the UI (dropdown, text input, etc.)
 * - Which pages/groups can use this filter
 *
 * ## Filter Properties Overview
 *
 * | Property | Purpose | Example |
 * |----------|---------|---------|
 * | `$name` | Unique identifier used in API requests | `'country'`, `'browser'` |
 * | `$column` | SQL column expression for WHERE clause | `'countries.code'`, `'sessions.user_id'` |
 * | `$type` | Data type for value sanitization | `'string'`, `'integer'`, `'boolean'` |
 * | `$inputType` | UI input component type | `'dropdown'`, `'searchable'`, `'text'` |
 * | `$options` | Static options for dropdown/multi-select | `[['value' => 'US', 'label' => 'United States']]` |
 * | `$groups` | Pages where filter is available | `['visitors', 'geographic']` |
 * | `$supportedOperators` | Allowed comparison operators | `['is', 'is_not', 'contains']` |
 * | `$joins` | Required table JOINs for this filter | Country filter needs countries table |
 * | `$requirement` | Required base table | `'sessions'` if filter needs sessions table |
 *
 * ## Usage Example (Frontend)
 *
 * ```typescript
 * // API Request with filters
 * {
 *   sources: ['visitors'],
 *   group_by: ['date'],
 *   filters: {
 *     country: { value: 'US', operator: 'is' },
 *     browser: { value: ['Chrome', 'Firefox'], operator: 'in' }
 *   }
 * }
 * ```
 *
 * @since 15.0.0
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * Filter name/identifier.
     *
     * This is the unique key used to identify the filter in API requests.
     * It should be lowercase, using underscores for multi-word names.
     *
     * Example values: 'country', 'browser', 'device_type', 'logged_in'
     *
     * Frontend usage:
     * ```typescript
     * filters: {
     *   country: { value: 'US', operator: 'is' }  // 'country' is the name
     * }
     * ```
     *
     * @var string
     */
    protected $name;

    /**
     * SQL column expression for the WHERE clause.
     *
     * This defines which database column the filter operates on.
     * Can include table aliases for JOINed tables.
     *
     * Examples:
     * - 'countries.code'           - Filter by country code
     * - 'sessions.user_id'         - Filter by user ID
     * - 'device_browsers.name'     - Filter by browser name
     * - 'views.viewed_at'          - Filter by view date
     *
     * The column is used in SQL like: WHERE {column} = 'value'
     *
     * @var string
     */
    protected $column;

    /**
     * Data type for value sanitization and SQL formatting.
     *
     * Determines how filter values are sanitized and formatted in SQL:
     *
     * | Type | Sanitization | SQL Format |
     * |------|--------------|------------|
     * | 'string' | esc_sql() | Quoted: 'value' |
     * | 'integer' | intval() | Unquoted: 123 |
     * | 'boolean' | Converts to 0/1 | Unquoted: 1 |
     * | 'date' | Date validation | Quoted: '2025-12-10' |
     * | 'datetime' | Datetime validation | Quoted: '2025-12-10 14:30:00' |
     *
     * @var string
     */
    protected $type = 'string';

    /**
     * JOIN configurations required for this filter.
     *
     * When filtering on a column from a related table, specify the JOIN here.
     * The query builder will add these JOINs automatically when the filter is used.
     *
     * Single JOIN format:
     * ```php
     * protected $joins = [
     *     'table' => 'countries',
     *     'alias' => 'countries',
     *     'on'    => 'sessions.country_id = countries.ID',
     *     'type'  => 'LEFT'  // LEFT, INNER, RIGHT
     * ];
     * ```
     *
     * Multiple JOINs format:
     * ```php
     * protected $joins = [
     *     ['table' => 'sessions', 'alias' => 'sessions', 'on' => '...', 'type' => 'LEFT'],
     *     ['table' => 'countries', 'alias' => 'countries', 'on' => '...', 'type' => 'LEFT'],
     * ];
     * ```
     *
     * @var array|null
     */
    protected $joins = null;

    /**
     * Required base table for this filter.
     *
     * Some filters require a specific table to be the primary table or
     * to be JOINed before the filter can work. Set this to ensure the
     * query builder includes the necessary table.
     *
     * Example: A filter on sessions.user_id requires 'sessions' table.
     *
     * @var string|null
     */
    protected $requirement = null;

    /**
     * Supported comparison operators for this filter.
     *
     * Defines which operators can be used with this filter in API requests.
     *
     * | Operator | SQL Output | Use Case |
     * |----------|------------|----------|
     * | 'is' | = 'value' | Exact match |
     * | 'is_not' | != 'value' | Exclude value |
     * | 'in' | IN ('a', 'b') | Match any of multiple values |
     * | 'not_in' | NOT IN ('a', 'b') | Exclude multiple values |
     * | 'contains' | LIKE '%value%' | Partial text match |
     * | 'starts_with' | LIKE 'value%' | Text starts with |
     * | 'ends_with' | LIKE '%value' | Text ends with |
     * | 'gt' | > value | Greater than (numbers) |
     * | 'gte' | >= value | Greater than or equal |
     * | 'lt' | < value | Less than |
     * | 'lte' | <= value | Less than or equal |
     *
     * Frontend usage:
     * ```typescript
     * filters: {
     *   country: { value: 'US', operator: 'is' },
     *   browser: { value: ['Chrome', 'Firefox'], operator: 'in' }
     * }
     * ```
     *
     * @var array
     */
    protected $supportedOperators = [
        'is', 'is_not', 'in', 'not_in',
        'contains', 'starts_with', 'ends_with'
    ];

    /**
     * Input type for frontend UI rendering.
     *
     * Determines which UI component the frontend should render for this filter.
     *
     * | Input Type | Component | Use Case |
     * |------------|-----------|----------|
     * | 'text' | Text input | Free-form text (IP address, search) |
     * | 'dropdown' | Select dropdown | Static list of options (Yes/No, device types) |
     * | 'searchable' | Autocomplete input | Large datasets with AJAX search (countries, cities) |
     * | 'multi-select' | Multi-select dropdown | Select multiple static options |
     * | 'date' | Date picker | Date filters |
     * | 'number' | Number input | Numeric filters (views count) |
     *
     * For 'dropdown' and 'multi-select': Define options in $options property.
     * For 'searchable': Override searchOptions() method for AJAX results.
     *
     * @var string
     */
    protected $inputType = 'text';

    /**
     * Static options for dropdown/multi-select input types.
     *
     * Define the available choices for dropdown and multi-select filters.
     * Each option must have 'value' (sent to API) and 'label' (shown to user).
     *
     * Format:
     * ```php
     * protected $options = [
     *     ['value' => 'desktop', 'label' => 'Desktop'],
     *     ['value' => 'mobile', 'label' => 'Mobile'],
     *     ['value' => 'tablet', 'label' => 'Tablet'],
     * ];
     * ```
     *
     * For translatable labels, override getOptions() method:
     * ```php
     * public function getOptions(): ?array
     * {
     *     return [
     *         ['value' => '1', 'label' => esc_html__('Yes', 'wp-statistics')],
     *         ['value' => '0', 'label' => esc_html__('No', 'wp-statistics')],
     *     ];
     * }
     * ```
     *
     * @var array|null
     */
    protected $options = null;

    /**
     * Groups/pages where this filter is available.
     *
     * Controls which sections of the UI can use this filter.
     * The frontend uses this to show/hide filters based on current page.
     *
     * Available groups:
     * - 'visitors'      - Visitor insight pages
     * - 'geographic'    - Geographic analytics pages
     * - 'content'       - Content analytics pages
     * - 'referrals'     - Referral analytics pages
     * - 'devices'       - Device analytics pages
     *
     * Example:
     * ```php
     * // Filter available on visitors and geographic pages
     * protected $groups = ['visitors', 'geographic'];
     * ```
     *
     * Frontend reads this via AdvancedFilters component:
     * ```tsx
     * <AdvancedFilters pageRoute="visitors" />
     * // Only shows filters where groups includes 'visitors'
     * ```
     *
     * @var array
     */
    protected $groups = [];

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
     * Get the groups where this filter is available.
     *
     * @return array
     */
    public function getGroups(): array
    {
        return $this->groups;
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
            'groups'             => $this->getGroups(),
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
