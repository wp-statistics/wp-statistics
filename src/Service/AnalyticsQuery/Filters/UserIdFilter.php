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
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('User ID', 'wp-statistics');
    }
}
