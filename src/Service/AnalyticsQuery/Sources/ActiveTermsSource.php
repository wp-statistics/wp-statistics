<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Active terms source - counts unique taxonomy terms that have content with views.
 *
 * This source counts distinct term IDs from content that has been
 * tracked by WP Statistics within the selected date range.
 *
 * For aggregate queries (no group_by), this uses a subquery to count
 * distinct terms from the resources and term tables.
 *
 * @since 15.0.0
 */
class ActiveTermsSource extends AbstractSource
{
    /**
     * Source name.
     *
     * @var string
     */
    protected $name = 'active_terms';

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
     * Returns a COUNT DISTINCT expression for active terms.
     *
     * Uses a subquery approach to count unique taxonomy terms with content
     * that has views, making it self-contained and not dependent on
     * outer query joins.
     *
     * Note: This counts all terms across all taxonomies by default.
     * Use with taxonomy_type filter to count terms in a specific taxonomy.
     */
    public function getExpression(): string
    {
        global $wpdb;

        $resourcesTable    = $wpdb->prefix . 'statistics_resources';
        $resourceUrisTable = $wpdb->prefix . 'statistics_resource_uris';
        $viewsTable        = $wpdb->prefix . 'statistics_views';
        $termTaxonomyTable = $wpdb->term_taxonomy;

        // Count distinct terms for content tracked by WP Statistics.
        // This is a self-contained subquery that works for aggregate queries.
        // Uses FIND_IN_SET to match term IDs in the cached_terms comma-separated list.
        return "COALESCE((
            SELECT COUNT(DISTINCT tt.term_id)
            FROM {$termTaxonomyTable} tt
            WHERE EXISTS (
                SELECT 1
                FROM {$resourcesTable} r
                INNER JOIN {$resourceUrisTable} ru ON ru.resource_id = r.ID
                INNER JOIN {$viewsTable} v ON v.resource_uri_id = ru.ID
                WHERE r.cached_terms IS NOT NULL
                AND r.cached_terms != ''
                AND FIND_IN_SET(tt.term_id, REPLACE(r.cached_terms, ' ', '')) > 0
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
