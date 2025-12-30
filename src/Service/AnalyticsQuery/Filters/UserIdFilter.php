<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * User ID filter - filters by WordPress user ID.
 *
 * @since 15.0.0
 */
class UserIdFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[user_id]=...
     */
    protected $name = 'user_id';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: sessions.user_id
     */
    protected $column = 'sessions.user_id';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer (WordPress user ID)
     */
    protected $type = 'integer';

    /**
     * UI input component type.
     *
     * @var string Input type: number
     */
    protected $inputType = 'number';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, is_null
     */
    protected $supportedOperators = ['is', 'is_not', 'is_null'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors', 'views'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('User ID', 'wp-statistics');
    }
}
