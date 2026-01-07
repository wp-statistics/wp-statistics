<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Active authors source - counts unique authors who have content with views.
 *
 * This source counts distinct author IDs from content that has been
 * tracked by WP Statistics within the selected date range.
 *
 * For aggregate queries (no group_by), this uses a subquery to count
 * distinct authors from the resources table.
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
     * Uses a subquery approach to count unique authors with content
     * that has views, making it self-contained and not dependent on
     * outer query joins.
     */
    public function getExpression(): string
    {
        global $wpdb;

        $resourcesTable    = $wpdb->prefix . 'statistics_resources';
        $resourceUrisTable = $wpdb->prefix . 'statistics_resource_uris';
        $viewsTable        = $wpdb->prefix . 'statistics_views';

        // Count distinct authors for content tracked by WP Statistics.
        // This is a self-contained subquery that works for aggregate queries.
        return "COALESCE((
            SELECT COUNT(DISTINCT r.cached_author_id)
            FROM {$resourcesTable} r
            INNER JOIN {$resourceUrisTable} ru ON ru.resource_id = r.ID
            INNER JOIN {$viewsTable} v ON v.resource_uri_id = ru.ID
            WHERE r.cached_author_id IS NOT NULL
            AND r.cached_author_id > 0
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
