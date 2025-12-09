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
    protected $supportedOperators = ['is', 'is_not'];
}
