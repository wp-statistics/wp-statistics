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
     * supports both taxonomy and author filtering:
     *
     * 1. Taxonomy-filtered queries (individual-category): Correlates on shared
     *    taxonomy terms via cached_terms to sum comments for all posts in the term.
     *
     * 2. Author-filtered queries (individual-author): Correlates on cached_author_id
     *    to sum comments for the filtered author's posts.
     *
     * Note: This requires the resources table to be joined via filters like
     * taxonomy filter, author filter, post_type filter, or group_by like 'page'.
     */
    public function getExpression(): string
    {
        global $wpdb;

        $resourcesTable = $wpdb->prefix . 'statistics_resources';
        $postsTable     = $wpdb->posts;

        // Sum comments for posts in the filtered result set.
        //
        // Uses dual correlation strategy:
        // 1. Term-based: When resources.cached_terms is set, correlates on shared terms
        //    to find all posts that share a term with the filtered resources.
        // 2. Author-based: When resources.cached_author_id is set, correlates on author
        //    to find all posts by the same author.
        //
        // For taxonomy-filtered queries (individual-category), all resources share
        // the filtered term, so term correlation correctly sums comments for all
        // posts in that term.
        //
        // For author-filtered queries (individual-author), author correlation ensures
        // we only count the filtered author's posts.
        //
        // Limitation: This sums ALL comments for matching content, not filtered by
        // date range. For date-filtered totals, use per-page results with PageGroupBy
        // post-processing.
        return "COALESCE((
            SELECT SUM(p.comment_count)
            FROM {$postsTable} p
            INNER JOIN {$resourcesTable} r ON p.ID = r.resource_id
            WHERE r.resource_type IN ('post', 'page')
            AND (
                (resources.cached_terms IS NOT NULL AND resources.cached_terms != '' AND
                 FIND_IN_SET(TRIM(SUBSTRING_INDEX(resources.cached_terms, ',', 1)), REPLACE(r.cached_terms, ' ', '')) > 0)
                OR
                ((resources.cached_terms IS NULL OR resources.cached_terms = '') AND
                 resources.cached_author_id IS NOT NULL AND r.cached_author_id = resources.cached_author_id)
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
