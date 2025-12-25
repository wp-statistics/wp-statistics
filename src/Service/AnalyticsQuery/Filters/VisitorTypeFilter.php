<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Visitor type filter - filters by visitor type (new vs returning).
 *
 * A visitor is considered "new" if they have only 1 session,
 * and "returning" if they have more than 1 session.
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
     * This is overridden by getColumn() to use dynamic table prefix.
     *
     * @var string Column path: subquery expression
     */
    protected $column = '';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer (1=returning, 0=new)
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
     * Get the SQL column for WHERE clause.
     *
     * Uses a subquery to count sessions for the visitor.
     * Expression evaluates to 1 for returning visitors (>1 session), 0 for new visitors (1 session)
     *
     * @return string
     */
    public function getColumn(): string
    {
        global $wpdb;
        $sessionsTable = $wpdb->prefix . 'statistics_sessions';
        // Returns 1 if visitor has more than 1 session (returning), 0 if exactly 1 session (new)
        return "(SELECT COUNT(*) FROM `{$sessionsTable}` vs_count WHERE vs_count.visitor_id = visitors.ID) > 1";
    }

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
            ['value' => 0, 'label' => esc_html__('New', 'wp-statistics')],
            ['value' => 1, 'label' => esc_html__('Returning', 'wp-statistics')],
        ];
    }
}
