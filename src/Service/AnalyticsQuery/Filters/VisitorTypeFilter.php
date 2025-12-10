<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Visitor type filter - filters by visitor type (new vs returning).
 *
 * @since 15.0.0
 */
class VisitorTypeFilter extends AbstractFilter
{
    protected $name               = 'visitor_type';
    protected $column             = 'visitors.is_new';
    protected $type               = 'string';
    protected $inputType          = 'dropdown';
    protected $supportedOperators = ['is'];
    protected $groups             = ['visitors'];

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
