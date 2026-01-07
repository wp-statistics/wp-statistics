<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Comments source - counts total comments for content with views.
 *
 * For aggregate queries (no group_by), this uses a subquery to sum
 * comment_count from WordPress posts that have views in the period.
 *
 * For per-page comment counts, use group_by: ['page'] which will
 * enrich results with comment_count via PageGroupBy post-processing.
 *
 * @since 15.0.0
 */
class CommentsSource extends AbstractSource
{
    /**
     * Source name.
     *
     * @var string
     */
    protected $name = 'comments';

    /**
     * SQL aggregation expression (placeholder, overridden by getExpression).
     *
     * @var string
     */
    protected $expression = '0';

    /**
     * Primary table required.
     *
     * @var string
     */
    protected $table = 'views';

    /**
     * Data type.
     *
     * @var string
     */
    protected $type = 'integer';

    /**
     * Format hint.
     *
     * @var string
     */
    protected $format = 'number';

    /**
     * {@inheritdoc}
     *
     * Returns a SUM expression for comment_count.
     *
     * For per-page queries (with group_by: ['page']), comments are added via
     * PageGroupBy post-processing. For aggregate queries, this expression
     * provides a placeholder that will be calculated via the joins added
     * by the page group_by or post_type filter.
     *
     * Note: For best results with aggregate comment totals, use with
     * group_by: ['page'] or ensure joins to resources table are present.
     */
    public function getExpression(): string
    {
        global $wpdb;

        $resourcesTable = $wpdb->prefix . 'statistics_resources';
        $postsTable     = $wpdb->posts;

        // Sum comments for all unique posts tracked by WP Statistics.
        // This is a self-contained subquery that doesn't depend on outer query
        // correlation, making it work for aggregate queries without GROUP BY.
        //
        // Limitation: This sums ALL comments for tracked content, not filtered
        // by date range. For date-filtered totals, the frontend can calculate
        // from per-page results which use PageGroupBy post-processing.
        return "COALESCE((
            SELECT SUM(p.comment_count)
            FROM {$postsTable} p
            WHERE p.ID IN (
                SELECT DISTINCT r.resource_id
                FROM {$resourcesTable} r
                WHERE r.resource_type IN ('post', 'page')
            )
        ), 0)";
    }

    /**
     * {@inheritdoc}
     *
     * Override to use getExpression() method instead of $expression property.
     */
    public function getExpressionWithAlias(): string
    {
        return $this->getExpression() . ' AS ' . $this->name;
    }
}
