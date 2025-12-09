<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Bounce rate source - percentage of single-page sessions.
 *
 * @since 15.0.0
 */
class BounceRateSource extends AbstractSource
{
    protected $name       = 'bounce_rate';
    protected $expression = 'ROUND(SUM(CASE WHEN sessions.total_views = 1 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(sessions.ID), 0), 1)';
    protected $table      = 'sessions';
    protected $type       = 'float';
    protected $format     = 'percent';
}
