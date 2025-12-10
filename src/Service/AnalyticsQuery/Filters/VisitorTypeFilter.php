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

    /** @var string Data type: string for visitor type matching (new/returning) */
    protected $type = 'string';

    /** @var string UI component: dropdown with New/Returning options */
    protected $inputType = 'dropdown';

    /** @var array Supported operators: exact match only for visitor type selection */
    protected $supportedOperators = ['is'];

    /** @var array Available on: visitors page for visitor segmentation */
    protected $groups = ['visitors'];

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
            ['value' => 'new', 'label' => esc_html__('New', 'wp-statistics')],
            ['value' => 'returning', 'label' => esc_html__('Returning', 'wp-statistics')],
        ];
    }
}
