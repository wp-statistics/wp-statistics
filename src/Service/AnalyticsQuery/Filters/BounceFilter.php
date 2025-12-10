<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Bounce filter - filters by bounce status.
 *
 * @since 15.0.0
 */
class BounceFilter extends AbstractFilter
{
    protected $name               = 'bounce';
    protected $column             = 'sessions.is_bounce';
    protected $type               = 'boolean';
    protected $inputType          = 'dropdown';
    protected $supportedOperators = ['is'];
    protected $groups             = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Bounce', 'wp-statistics');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): ?array
    {
        return [
            ['value' => '1', 'label' => esc_html__('Yes', 'wp-statistics')],
            ['value' => '0', 'label' => esc_html__('No', 'wp-statistics')],
        ];
    }
}
