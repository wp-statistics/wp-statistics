<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Bounce filter - filters by bounce status.
 *
 * @since 15.0.0
 */
class BounceFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[bounce]=... */
    protected $name = 'bounce';

    /** @var string SQL column: boolean flag indicating if session was a single-page visit */
    protected $column = 'sessions.is_bounce';

    /** @var string Data type: boolean (1=bounced, 0=engaged) */
    protected $type = 'boolean';

    /** @var string UI component: dropdown with Yes/No options */
    protected $inputType = 'dropdown';

    /** @var array Supported operators: exact match only for boolean values */
    protected $supportedOperators = ['is'];

    /** @var array Available on: visitors page for analyzing engagement */
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
