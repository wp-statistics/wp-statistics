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
     * PageGroupBy post-processing which is more reliable.
     *
     * For aggregate queries, this expression uses a correlated subquery that
     * respects the outer query's author filter by correlating on cached_author_id.
     * This ensures that only the filtered author's content comments are counted.
     *
     * Note: This requires the resources table to be joined via filters like
     * author filter, post_type filter, or group_by like 'page'.
     */
    public function getExpression(): string
    {
        global $wpdb;

        $resourcesTable = $wpdb->prefix . 'statistics_resources';
        $postsTable     = $wpdb->posts;

        // Sum comments for posts that match the filtered author.
        // This correlated subquery references resources.cached_author_id from
        // the outer query (joined via author filter) to ensure we only count
        // comments for the filtered author's tracked content.
        //
        // When author filter is applied, resources table is joined and filtered,
        // so resources.cached_author_id contains the filtered author's ID.
        //
        // Limitation: This sums ALL comments for the author's tracked content,
        // not filtered by date range. For date-filtered totals, use per-page
        // results with PageGroupBy post-processing.
        return "COALESCE((
            SELECT SUM(p.comment_count)
            FROM {$postsTable} p
            INNER JOIN {$resourcesTable} r ON p.ID = r.resource_id
            WHERE r.cached_author_id = resources.cached_author_id
            AND r.resource_type IN ('post', 'page')
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
