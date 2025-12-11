<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Visitor type filter - filters by visitor type (new vs returning).
 *
 * @since 15.0.0
 */
class VisitorTypeFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[visitor_type]=... */
    protected $name = 'visitor_type';

    /** @var string SQL column: new visitor flag from visitors table (determines new vs returning) */
    protected $column = 'visitors.is_new';

    /** @var string Data type: integer for boolean flag (1=new, 0=returning) */
    protected $type = 'integer';

    /** @var string UI component: dropdown with New/Returning options */
    protected $inputType = 'dropdown';

    /** @var array Supported operators: exact match only for visitor type selection */
    protected $supportedOperators = ['is'];

    /** @var array Available on: visitors page for visitor segmentation */
    protected $groups = ['visitors'];

    /** @var string Required base table: needs sessions table to join visitors */
    protected $requirement = 'sessions';

    /** @var array JOIN configuration to visitors table */
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
