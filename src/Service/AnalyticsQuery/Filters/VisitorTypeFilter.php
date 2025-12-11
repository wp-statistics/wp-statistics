<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Visitor type filter - filters by visitor type (new vs returning).
 *
 * @since 15.0.0
 */
class VisitorTypeFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[visitor_type]=...
     */
    protected $name = 'visitor_type';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: visitors.is_new
     */
    protected $column = 'visitors.is_new';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer (1=new, 0=returning)
     */
    protected $type = 'integer';

    /**
     * UI input component type.
     *
     * @var string Input type: dropdown
     */
    protected $inputType = 'dropdown';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is
     */
    protected $supportedOperators = ['is'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors'];

    /**
     * Required base table to enable this filter.
     *
     * @var string|null Table name: sessions
     */
    protected $requirement = 'sessions';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> visitors
     */
    protected $joins = [
        'table' => 'visitors',
        'alias' => 'visitors',
        'on'    => 'sessions.visitor_id = visitors.ID',
        'type'  => 'LEFT',
    ];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Visitor Type', 'wp-statistics');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): ?array
    {
        return [
            ['value' => 1, 'label' => esc_html__('New', 'wp-statistics')],
            ['value' => 0, 'label' => esc_html__('Returning', 'wp-statistics')],
        ];
    }
}
