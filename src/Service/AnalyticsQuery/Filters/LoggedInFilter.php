<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Logged in filter - filters by user login status.
 *
 * @since 15.0.0
 */
class LoggedInFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[logged_in]=...
     */
    protected $name = 'logged_in';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: sessions.user_id
     */
    protected $column = 'sessions.user_id';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: boolean
     */
    protected $type = 'boolean';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is
     */
    protected $supportedOperators = ['is'];

    /**
     * UI input component type.
     *
     * @var string Input type: dropdown
     */
    protected $inputType = 'dropdown';

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors', 'views', 'individual-content'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Login Status', 'wp-statistics');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): ?array
    {
        return [
            ['value' => '1', 'label' => esc_html__('Logged-in', 'wp-statistics')],
            ['value' => '0', 'label' => esc_html__('Anonymous', 'wp-statistics')],
        ];
    }
}
