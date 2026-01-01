<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Visitor status source - returns 'new' or 'returning' based on lifetime session count.
 *
 * A visitor is considered 'new' if they have only 1 session ever (across all time),
 * and 'returning' if they have multiple sessions.
 *
 * Note: This source requires the 'visitor' groupBy to function properly, as it needs
 * access to visitors.ID to count lifetime sessions.
 *
 * @since 15.0.0
 */
class VisitorStatusSource extends AbstractSource
{
    protected $name       = 'visitor_status';
    protected $table      = 'sessions';
    protected $type       = 'string';
    protected $format     = 'text';

    /**
     * Get the SQL expression for visitor status.
     *
     * Uses a subquery to count ALL sessions for the visitor regardless of date filters.
     *
     * @return string
     */
    public function getExpression(): string
    {
        global $wpdb;
        $sessionsTable = $wpdb->prefix . 'statistics_sessions';

        return "(
            CASE
                WHEN (SELECT COUNT(*) FROM {$sessionsTable} s WHERE s.visitor_id = visitors.ID) = 1
                THEN 'new'
                ELSE 'returning'
            END
        )";
    }

    /**
     * Get the SQL expression with alias.
     *
     * Override to ensure the expression is properly generated with table prefix.
     *
     * @return string
     */
    public function getExpressionWithAlias(): string
    {
        return $this->getExpression() . ' AS ' . $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsSummaryTable(): bool
    {
        return false; // This requires lifetime data and visitor context, not summary tables
    }
}
