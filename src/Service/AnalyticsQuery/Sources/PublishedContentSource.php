<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Published content source - counts unique content items that received views.
 *
 * This source counts distinct resources (posts, pages, etc.) that have
 * been tracked by WP Statistics within the selected date range.
 *
 * Note: This counts content with views in the period, not content published in the period.
 * For accurate "published in period" counts, use with post_type filter and date filters.
 *
 * @since 15.0.0
 */
class PublishedContentSource extends AbstractSource
{
    /**
     * Source name.
     *
     * @var string
     */
    protected $name = 'published_content';

    /**
     * SQL aggregation expression.
     * Counts unique resource IDs (WordPress post IDs) from views table.
     *
     * @var string
     */
    protected $expression = 'COUNT(DISTINCT views.resource_id)';

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
}
