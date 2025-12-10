<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Logged in filter - filters by user login status.
 *
 * @since 15.0.0
 */
class LoggedInFilter extends AbstractFilter
{
    protected $name               = 'logged_in';
    protected $column             = 'sessions.user_id';
    protected $type               = 'boolean';
    protected $supportedOperators = ['is'];

    protected $inputType = 'dropdown';
    protected $groups    = ['visitors'];

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
