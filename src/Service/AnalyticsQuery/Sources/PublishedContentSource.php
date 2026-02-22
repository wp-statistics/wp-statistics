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
     * Extract term ID from taxonomy filter if present.
     *
     * Handles both array format (with 'is' key) and scalar format.
     *
     * @return int|null The term ID or null if not set
     */
    private function getTermIdFromFilter(): ?int
    {
        if (empty($this->filters['taxonomy'])) {
            return null;
        }

        $termId = is_array($this->filters['taxonomy'])
            ? ($this->filters['taxonomy']['is'] ?? reset($this->filters['taxonomy']))
            : $this->filters['taxonomy'];

        return (int) $termId;
    }

    /**
     * Extract author ID from author filter if present.
     *
     * Handles both array format (with 'is' key) and scalar format.
     *
     * @return int|null The author ID or null if not set
     */
    private function getAuthorIdFromFilter(): ?int
    {
        if (empty($this->filters['author'])) {
            return null;
        }

        $authorId = is_array($this->filters['author'])
            ? ($this->filters['author']['is'] ?? reset($this->filters['author']))
            : $this->filters['author'];

        return (int) $authorId;
    }

    /**
     * Build SQL expression for term-filtered post count.
     *
     * Creates a correlated subquery that counts posts belonging to a specific term,
     * filtered by the provided date WHERE clause.
     *
     * @param int    $termId          The term ID to filter by
     * @param string $dateWhereClause SQL WHERE clause for date filtering
     * @return string SQL expression
     */
    private function buildTermFilteredExpression(int $termId, string $dateWhereClause): string
    {
        global $wpdb;
        $postsTable = $wpdb->posts;
        $termRelTable = $wpdb->term_relationships;
        $termTaxTable = $wpdb->term_taxonomy;
        $postTypeClause = $this->getPostTypeClause('p.post_type');

        return "COALESCE((
            SELECT COUNT(DISTINCT p.ID)
            FROM {$postsTable} p
            INNER JOIN {$termRelTable} tr ON p.ID = tr.object_id
            INNER JOIN {$termTaxTable} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE {$dateWhereClause}
            AND p.post_status = 'publish'
            AND {$postTypeClause}
            AND tt.term_id = {$termId}
        ), 0)";
    }

    /**
     * Build SQL expression for taxonomy-type filtered post count.
     *
     * Creates a correlated subquery that counts posts belonging to any term
     * in the specified taxonomy, filtered by the provided date WHERE clause.
     *
     * @param string $taxonomy             The taxonomy type (e.g., 'category', 'post_tag')
     * @param string $dateWhereClause      SQL WHERE clause for date filtering
     * @param string $additionalWhereClause Optional additional WHERE conditions
     * @return string SQL expression
     */
    private function buildTaxonomyFilteredExpression(string $taxonomy, string $dateWhereClause, string $additionalWhereClause = ''): string
    {
        global $wpdb;
        $postsTable = $wpdb->posts;
        $termRelTable = $wpdb->term_relationships;
        $termTaxTable = $wpdb->term_taxonomy;
        $postTypeClause = $this->getPostTypeClause('p.post_type');
        $taxonomySafe = esc_sql($taxonomy);

        $additionalClause = $additionalWhereClause ? "\n            AND {$additionalWhereClause}" : '';

        return "COALESCE((
            SELECT COUNT(DISTINCT p.ID)
            FROM {$postsTable} p
            INNER JOIN {$termRelTable} tr ON p.ID = tr.object_id
            INNER JOIN {$termTaxTable} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE {$dateWhereClause}
            AND p.post_status = 'publish'
            AND {$postTypeClause}
            AND tt.taxonomy = '{$taxonomySafe}'{$additionalClause}
        ), 0)";
    }

    /**
     * Build SQL expression for author-filtered post count.
     *
     * Creates a subquery that counts posts by a specific author,
     * filtered by the provided date WHERE clause. Also respects
     * taxonomy and taxonomy_type filters if present.
     *
     * @param int    $authorId        The author ID to filter by
     * @param string $dateWhereClause SQL WHERE clause for date filtering
     * @return string SQL expression
     */
    private function buildAuthorFilteredExpression(int $authorId, string $dateWhereClause): string
    {
        global $wpdb;
        $postsTable = $wpdb->posts;
        $postTypeClause = $this->getPostTypeClause('p.post_type');

        // Check if taxonomy_type filter is also present - combine with author filter
        if (!empty($this->filters['taxonomy_type']['is'])) {
            return $this->buildTaxonomyFilteredExpression(
                $this->filters['taxonomy_type']['is'],
                $dateWhereClause,
                "p.post_author = {$authorId}"
            );
        }

        // Check if taxonomy filter is present (specific term_id) - combine with author filter
        $termId = $this->getTermIdFromFilter();
        if ($termId !== null) {
            $termRelTable = $wpdb->term_relationships;
            $termTaxTable = $wpdb->term_taxonomy;

            return "COALESCE((
                SELECT COUNT(DISTINCT p.ID)
                FROM {$postsTable} p
                INNER JOIN {$termRelTable} tr ON p.ID = tr.object_id
                INNER JOIN {$termTaxTable} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE {$dateWhereClause}
                AND p.post_status = 'publish'
                AND {$postTypeClause}
                AND tt.term_id = {$termId}
                AND p.post_author = {$authorId}
            ), 0)";
        }

        // Author filter only
        return "COALESCE((
            SELECT COUNT(*)
            FROM {$postsTable} p
            WHERE {$dateWhereClause}
            AND p.post_status = 'publish'
            AND {$postTypeClause}
            AND p.post_author = {$authorId}
        ), 0)";
    }

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

        if ($this->hasContextDimension('category')
            || $this->hasContextDimension('post_tag')
            || $this->hasContextDimension('taxonomy')) {
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

        // Check if author filter is present
        $authorId = $this->getAuthorIdFromFilter();
        if ($authorId !== null) {
            return $this->getAuthorFilteredDateExpression($authorId);
        }

        // Check if taxonomy filter is present (specific term_id)
        $termId = $this->getTermIdFromFilter();
        if ($termId !== null) {
            return $this->getTermFilteredDateExpression($termId);
        }

        // Check if taxonomy_type filter is present
        if (!empty($this->filters['taxonomy_type']['is'])) {
            return $this->getTaxonomyFilteredDateExpression($this->filters['taxonomy_type']['is']);
        }

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
     * Count posts published on a specific day that belong to a specific term.
     *
     * @param int $termId The term ID to filter by
     * @return string SQL expression
     */
    private function getTermFilteredDateExpression(int $termId): string
    {
        return $this->buildTermFilteredExpression(
            $termId,
            "DATE(p.post_date) = DATE(MIN(sessions.started_at))"
        );
    }

    /**
     * Count posts published on a specific day by a specific author.
     *
     * @param int $authorId The author ID to filter by
     * @return string SQL expression
     */
    private function getAuthorFilteredDateExpression(int $authorId): string
    {
        return $this->buildAuthorFilteredExpression(
            $authorId,
            "DATE(p.post_date) = DATE(MIN(sessions.started_at))"
        );
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

        // Check if author filter is present
        $authorId = $this->getAuthorIdFromFilter();
        if ($authorId !== null) {
            return $this->getAuthorFilteredWeekExpression($authorId);
        }

        // Check if taxonomy filter is present (specific term_id)
        $termId = $this->getTermIdFromFilter();
        if ($termId !== null) {
            return $this->getTermFilteredWeekExpression($termId);
        }

        // Check if taxonomy_type filter is present
        if (!empty($this->filters['taxonomy_type']['is'])) {
            return $this->getTaxonomyFilteredWeekExpression($this->filters['taxonomy_type']['is']);
        }

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
     * Count posts published in a specific week that belong to a specific term.
     *
     * @param int $termId The term ID to filter by
     * @return string SQL expression
     */
    private function getTermFilteredWeekExpression(int $termId): string
    {
        return $this->buildTermFilteredExpression(
            $termId,
            "YEARWEEK(p.post_date, 1) = YEARWEEK(MIN(sessions.started_at), 1)"
        );
    }

    /**
     * Count posts published in a specific week by a specific author.
     *
     * @param int $authorId The author ID to filter by
     * @return string SQL expression
     */
    private function getAuthorFilteredWeekExpression(int $authorId): string
    {
        return $this->buildAuthorFilteredExpression(
            $authorId,
            "YEARWEEK(p.post_date, 1) = YEARWEEK(MIN(sessions.started_at), 1)"
        );
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

        // Check if author filter is present
        $authorId = $this->getAuthorIdFromFilter();
        if ($authorId !== null) {
            return $this->getAuthorFilteredMonthExpression($authorId);
        }

        // Check if taxonomy filter is present (specific term_id)
        $termId = $this->getTermIdFromFilter();
        if ($termId !== null) {
            return $this->getTermFilteredMonthExpression($termId);
        }

        // Check if taxonomy_type filter is present
        if (!empty($this->filters['taxonomy_type']['is'])) {
            return $this->getTaxonomyFilteredMonthExpression($this->filters['taxonomy_type']['is']);
        }

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
     * Count posts published in a specific month that belong to a specific term.
     *
     * @param int $termId The term ID to filter by
     * @return string SQL expression
     */
    private function getTermFilteredMonthExpression(int $termId): string
    {
        return $this->buildTermFilteredExpression(
            $termId,
            "YEAR(p.post_date) = YEAR(MIN(sessions.started_at)) AND MONTH(p.post_date) = MONTH(MIN(sessions.started_at))"
        );
    }

    /**
     * Count posts published in a specific month by a specific author.
     *
     * @param int $authorId The author ID to filter by
     * @return string SQL expression
     */
    private function getAuthorFilteredMonthExpression(int $authorId): string
    {
        return $this->buildAuthorFilteredExpression(
            $authorId,
            "YEAR(p.post_date) = YEAR(MIN(sessions.started_at)) AND MONTH(p.post_date) = MONTH(MIN(sessions.started_at))"
        );
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

        // Check if taxonomy_type filter is present
        if (!empty($this->filters['taxonomy_type']['is'])) {
            return $this->getTaxonomyFilteredAuthorExpression($this->filters['taxonomy_type']['is']);
        }

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

        // Check if author filter is present - filter posts by this specific author
        // This is used for single author pages where we need posts for that specific author
        $authorId = $this->getAuthorIdFromFilter();
        if ($authorId !== null) {
            return $this->getAuthorFilteredAggregateExpression($authorId);
        }

        // Check if taxonomy filter is present (specific term_id) - this takes priority
        // This is used for single category/tag pages where we need posts for that specific term
        $termId = $this->getTermIdFromFilter();
        if ($termId !== null) {
            return $this->getTermFilteredAggregateExpression($termId);
        }

        // Check if taxonomy_type filter is present - if so, count only posts with that taxonomy
        if (!empty($this->filters['taxonomy_type']['is'])) {
            return $this->getTaxonomyFilteredAggregateExpression($this->filters['taxonomy_type']['is']);
        }

        return "COALESCE((
            SELECT COUNT(*)
            FROM {$postsTable} p
            WHERE {$dateClause}
            AND p.post_status = 'publish'
            AND {$postTypeClause}
        ), 0)";
    }

    /**
     * Count posts that belong to a specific term (aggregate mode).
     *
     * @param int $termId The term ID to filter by
     * @return string SQL expression
     */
    private function getTermFilteredAggregateExpression(int $termId): string
    {
        return $this->buildTermFilteredExpression($termId, $this->getFullRangeDateClause());
    }

    /**
     * Count posts that belong to a specific author (aggregate mode).
     *
     * @param int $authorId The author ID to filter by
     * @return string SQL expression
     */
    private function getAuthorFilteredAggregateExpression(int $authorId): string
    {
        return $this->buildAuthorFilteredExpression($authorId, $this->getFullRangeDateClause());
    }

    /**
     * Count posts that have terms in the specified taxonomy (aggregate mode).
     *
     * @param string $taxonomy The taxonomy type (e.g., 'category', 'post_tag')
     * @return string SQL expression
     */
    private function getTaxonomyFilteredAggregateExpression(string $taxonomy): string
    {
        return $this->buildTaxonomyFilteredExpression($taxonomy, $this->getFullRangeDateClause());
    }

    /**
     * Count posts published on a specific day that have terms in the specified taxonomy.
     *
     * @param string $taxonomy The taxonomy type (e.g., 'category', 'post_tag')
     * @return string SQL expression
     */
    private function getTaxonomyFilteredDateExpression(string $taxonomy): string
    {
        return $this->buildTaxonomyFilteredExpression(
            $taxonomy,
            "DATE(p.post_date) = DATE(MIN(sessions.started_at))"
        );
    }

    /**
     * Count posts published in a specific week that have terms in the specified taxonomy.
     *
     * @param string $taxonomy The taxonomy type (e.g., 'category', 'post_tag')
     * @return string SQL expression
     */
    private function getTaxonomyFilteredWeekExpression(string $taxonomy): string
    {
        return $this->buildTaxonomyFilteredExpression(
            $taxonomy,
            "YEARWEEK(p.post_date, 1) = YEARWEEK(MIN(sessions.started_at), 1)"
        );
    }

    /**
     * Count posts published in a specific month that have terms in the specified taxonomy.
     *
     * @param string $taxonomy The taxonomy type (e.g., 'category', 'post_tag')
     * @return string SQL expression
     */
    private function getTaxonomyFilteredMonthExpression(string $taxonomy): string
    {
        return $this->buildTaxonomyFilteredExpression(
            $taxonomy,
            "YEAR(p.post_date) = YEAR(MIN(sessions.started_at)) AND MONTH(p.post_date) = MONTH(MIN(sessions.started_at))"
        );
    }

    /**
     * Count posts per author that have terms in the specified taxonomy.
     *
     * @param string $taxonomy The taxonomy type (e.g., 'category', 'post_tag')
     * @return string SQL expression
     */
    private function getTaxonomyFilteredAuthorExpression(string $taxonomy): string
    {
        return $this->buildTaxonomyFilteredExpression(
            $taxonomy,
            $this->getFullRangeDateClause(),
            "p.post_author = resources.cached_author_id"
        );
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
