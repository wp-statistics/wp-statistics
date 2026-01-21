<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Published content source - counts WordPress posts by publish date.
 *
 * Context-aware expressions:
 * - Date/week/month context: Counts posts published on that specific day/week/month
 * - Author context: Counts posts per author in date range
 * - Taxonomy context: Counts posts per term in date range
 * - Aggregate (no grouping): Counts total posts in date range
 *
 * Post type handling:
 * - Uses post_type filter value when applied
 * - Falls back to all public queryable types (supports custom types)
 *
 * @since 15.0.0
 */
class PublishedContentSource extends AbstractSource
{
    protected $name = 'published_content';
    protected $expression = '0';
    protected $table = 'views';
    protected $type = 'integer';
    protected $format = 'number';

    /**
     * {@inheritdoc}
     */
    public function getExpression(): string
    {
        // Time-based grouping: count posts for that specific period
        if ($this->hasContextDimension('date')) {
            return $this->getDateGroupedExpression();
        }

        if ($this->hasContextDimension('week')) {
            return $this->getWeekGroupedExpression();
        }

        if ($this->hasContextDimension('month')) {
            return $this->getMonthGroupedExpression();
        }

        // Entity-based grouping: count posts for the entity in full date range
        if ($this->hasContextDimension('author')) {
            return $this->getAuthorContextExpression();
        }

        if ($this->hasContextDimension('category') || $this->hasContextDimension('post_tag')) {
            return $this->getTermContextExpression();
        }

        // Aggregate (no grouping): count all posts in full date range
        return $this->getAggregateExpression();
    }

    /**
     * Count posts published on a specific day (date-grouped queries).
     *
     * Uses the session's date to count posts for that exact day.
     *
     * @return string
     */
    private function getDateGroupedExpression(): string
    {
        global $wpdb;
        $postsTable = $wpdb->posts;
        $postTypeClause = $this->getPostTypeClause('p.post_type');

        // Use session date to count posts for that specific day
        return "COALESCE((
            SELECT COUNT(*)
            FROM {$postsTable} p
            WHERE DATE(p.post_date) = DATE(MIN(sessions.started_at))
            AND p.post_status = 'publish'
            AND {$postTypeClause}
        ), 0)";
    }

    /**
     * Count posts published in a specific week (week-grouped queries).
     *
     * @return string
     */
    private function getWeekGroupedExpression(): string
    {
        global $wpdb;
        $postsTable = $wpdb->posts;
        $postTypeClause = $this->getPostTypeClause('p.post_type');

        // Use session date to determine the week, count posts in that week
        return "COALESCE((
            SELECT COUNT(*)
            FROM {$postsTable} p
            WHERE YEARWEEK(p.post_date, 1) = YEARWEEK(MIN(sessions.started_at), 1)
            AND p.post_status = 'publish'
            AND {$postTypeClause}
        ), 0)";
    }

    /**
     * Count posts published in a specific month (month-grouped queries).
     *
     * @return string
     */
    private function getMonthGroupedExpression(): string
    {
        global $wpdb;
        $postsTable = $wpdb->posts;
        $postTypeClause = $this->getPostTypeClause('p.post_type');

        // Use session date to determine the month, count posts in that month
        return "COALESCE((
            SELECT COUNT(*)
            FROM {$postsTable} p
            WHERE YEAR(p.post_date) = YEAR(MIN(sessions.started_at))
            AND MONTH(p.post_date) = MONTH(MIN(sessions.started_at))
            AND p.post_status = 'publish'
            AND {$postTypeClause}
        ), 0)";
    }

    /**
     * Count posts per author in the full date range.
     *
     * @return string
     */
    private function getAuthorContextExpression(): string
    {
        global $wpdb;
        $postsTable = $wpdb->posts;
        $postTypeClause = $this->getPostTypeClause('p.post_type');
        $dateClause = $this->getFullRangeDateClause();

        return "COALESCE((
            SELECT COUNT(*)
            FROM {$postsTable} p
            WHERE {$dateClause}
            AND p.post_status = 'publish'
            AND {$postTypeClause}
            AND p.post_author = resources.cached_author_id
        ), 0)";
    }

    /**
     * Count posts per term in the full date range.
     *
     * @return string
     */
    private function getTermContextExpression(): string
    {
        global $wpdb;
        $postsTable = $wpdb->posts;
        $termRelTable = $wpdb->term_relationships;
        $termTaxTable = $wpdb->term_taxonomy;
        $postTypeClause = $this->getPostTypeClause('p.post_type');
        $dateClause = $this->getFullRangeDateClause();

        return "COALESCE((
            SELECT COUNT(DISTINCT p.ID)
            FROM {$postsTable} p
            INNER JOIN {$termRelTable} tr ON p.ID = tr.object_id
            INNER JOIN {$termTaxTable} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE {$dateClause}
            AND p.post_status = 'publish'
            AND {$postTypeClause}
            AND tt.term_id = terms.term_id
        ), 0)";
    }

    /**
     * Count total posts in the full date range (aggregate queries).
     *
     * @return string
     */
    private function getAggregateExpression(): string
    {
        global $wpdb;
        $postsTable = $wpdb->posts;
        $postTypeClause = $this->getPostTypeClause('p.post_type');
        $dateClause = $this->getFullRangeDateClause();

        return "COALESCE((
            SELECT COUNT(*)
            FROM {$postsTable} p
            WHERE {$dateClause}
            AND p.post_status = 'publish'
            AND {$postTypeClause}
        ), 0)";
    }

    /**
     * Get date clause for the full query range.
     *
     * Used for aggregate queries and entity-based grouping (author, taxonomy).
     *
     * @return string SQL clause for date filtering
     */
    private function getFullRangeDateClause(): string
    {
        $dateFrom = $this->dateFrom ? esc_sql($this->dateFrom) : date('Y-m-d');
        $dateTo = $this->dateTo ? esc_sql($this->dateTo) : date('Y-m-d');

        return "DATE(p.post_date) >= '{$dateFrom}' AND DATE(p.post_date) <= '{$dateTo}'";
    }

    /**
     * {@inheritdoc}
     */
    public function getExpressionWithAlias(): string
    {
        return $this->getExpression() . ' AS ' . $this->name;
    }
}
