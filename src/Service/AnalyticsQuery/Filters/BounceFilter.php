<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Bounce filter - filters by bounce status.
 *
 * @since 15.0.0
 */
class BounceFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[bounce]=...
     */
    protected $name = 'bounce';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: sessions.is_bounce
     */
    protected $column = 'sessions.is_bounce';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: boolean (1=bounced, 0=engaged)
     */
    protected $type = 'boolean';

    /**
     * UI input component type.
     *
     * @var string Input type: dropdown with Yes/No options
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
