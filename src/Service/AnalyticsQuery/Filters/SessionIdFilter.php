<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Session ID filter - filters views by session.
 *
 * Used for filtering page views within a specific session (e.g., for session
 * page view subtables in the single visitor report).
 *
 * @since 15.0.0
 */
class SessionIdFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[session_id]=...
     */
    protected $name = 'session_id';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: views.session_id
     */
    protected $column = 'views.session_id';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer (session ID)
     */
    protected $type = 'integer';

    /**
     * UI input component type.
     *
     * @var string Input type: number (internal filter, not typically shown in UI)
     */
    protected $inputType = 'number';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not
     */
    protected $supportedOperators = ['is', 'is_not'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: views (internal use for session subtables)
     */
    protected $groups = ['views'];

    /**
     * Table requirement for this filter.
     *
     * @var string Requirement: views table must be available
     */
    protected $requirement = 'views';

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Session ID', 'wp-statistics');
    }
}
