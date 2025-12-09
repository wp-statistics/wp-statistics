<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Session ID filter - filters by session ID.
 *
 * @since 15.0.0
 */
class SessionIdFilter extends AbstractFilter
{
    protected $name               = 'session_id';
    protected $column             = 'sessions.ID';
    protected $type               = 'integer';
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];
}
