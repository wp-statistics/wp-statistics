<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Session ID filter - filters by session ID.
 *
 * @since 15.0.0
 */
class SessionIdFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[session_id]=... */
    protected $name = 'session_id';

    /** @var string SQL column: primary key ID of sessions table */
    protected $column = 'sessions.ID';

    /** @var string Data type: integer for session ID matching */
    protected $type = 'integer';

    /** @var array Supported operators: exact match, exclusion, set membership, and numeric comparisons */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Session ID', 'wp-statistics');
    }
}
