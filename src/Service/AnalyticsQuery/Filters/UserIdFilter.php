<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * User ID filter - filters by WordPress user ID.
 *
 * @since 15.0.0
 */
class UserIdFilter extends AbstractFilter
{
    protected $name               = 'user_id';
    protected $column             = 'sessions.user_id';
    protected $type               = 'integer';
    protected $inputType          = 'number';
    protected $supportedOperators = ['is', 'is_not', 'is_null'];
    protected $pages              = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('User ID', 'wp-statistics');
    }
}
