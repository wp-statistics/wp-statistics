<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Visitor hash filter - filters by visitor hash identifier.
 *
 * @since 15.0.0
 */
class VisitorHashFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[visitor_hash]=...
     */
    protected $name = 'visitor_hash';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: visitors.hash
     */
    protected $column = 'visitors.hash';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * UI input component type.
     *
     * @var string Input type: text
     */
    protected $inputType = 'text';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, contains, starts_with
     */
    protected $supportedOperators = ['is', 'is_not', 'contains', 'starts_with'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors'];

    /**
     * JOIN to visitors table (needed when sessions is primary table).
     *
     * @var array
     */
    protected $joins = [
        'table' => 'visitors',
        'alias' => 'visitors',
        'on'    => 'sessions.visitor_id = visitors.ID',
        'type'  => 'INNER',
    ];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Visitor Hash', 'wp-statistics');
    }
}
