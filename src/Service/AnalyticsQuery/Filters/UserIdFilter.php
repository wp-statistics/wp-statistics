<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * User ID filter - filters by WordPress user ID.
 *
 * @since 15.0.0
 */
class UserIdFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[user_id]=... */
    protected $name = 'user_id';

    /** @var string SQL column: WordPress user ID from sessions table (NULL for anonymous visitors) */
    protected $column = 'sessions.user_id';

    /** @var string Data type: integer for WordPress user ID matching */
    protected $type = 'integer';

    /** @var string UI component: number input for user ID entry */
    protected $inputType = 'number';

    /** @var array Supported operators: exact match, exclusion, and null check */
    protected $supportedOperators = ['is', 'is_not', 'is_null'];

    /** @var array Available on: visitors page for user tracking */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('User ID', 'wp-statistics');
    }
}
