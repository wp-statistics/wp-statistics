<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Active authors source - counts unique authors who published content.
 *
 * This source counts distinct author IDs who have published content
 * of the selected post type within the selected date range.
 *
 * For aggregate queries (no group_by), this uses a subquery to count
 * distinct authors from the WordPress posts table.
 *
 * @since 15.0.0
 */
class ActiveAuthorsSource extends AbstractSource
{
    /**
     * Source name.
     *
     * @var string
     */
    protected $name = 'active_authors';

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
     * Returns a COUNT DISTINCT expression for active authors.
     *
     * Counts authors who published content of the selected post type
     * within the selected date range from wp_posts.
     */
    public function getExpression(): string
    {
        global $wpdb;

        $postsTable     = $wpdb->posts;
        $postTypeClause = $this->getPostTypeClause('p.post_type');
        $dateClause     = $this->getDateRangeClause();

        // Count distinct authors who published content in the date range.
        return "COALESCE((
            SELECT COUNT(DISTINCT p.post_author)
            FROM {$postsTable} p
            WHERE p.post_status = 'publish'
            AND p.post_author > 0
            AND {$postTypeClause}
            {$dateClause}
        ), 0)";
    }

    /**
     * Get date range clause for the query.
     *
     * @return string SQL clause for date filtering, or empty string if no dates
     */
    private function getDateRangeClause(): string
    {
        if ($this->dateFrom && $this->dateTo) {
            $dateFrom = esc_sql($this->dateFrom);
            $dateTo   = esc_sql($this->dateTo);
            return "AND DATE(p.post_date) >= '{$dateFrom}' AND DATE(p.post_date) <= '{$dateTo}'";
        }
        return '';
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
