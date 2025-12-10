<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Logged in filter - filters by user login status.
 *
 * @since 15.0.0
 */
class LoggedInFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[logged_in]=... */
    protected $name = 'logged_in';

    /** @var string SQL column: user_id presence indicates logged-in status (NULL=anonymous) */
    protected $column = 'sessions.user_id';

    /** @var string Data type: boolean (converts to NULL check in SQL) */
    protected $type = 'boolean';

    /** @var array Supported operators: exact match only for login status */
    protected $supportedOperators = ['is'];

    /** @var string UI component: dropdown with Logged-in/Anonymous options */
    protected $inputType = 'dropdown';

    /** @var array Available on: visitors page for user authentication analysis */
    protected $groups = ['visitors'];

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
