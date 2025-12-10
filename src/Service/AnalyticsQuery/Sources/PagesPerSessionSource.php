<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Pages per session source.
 *
 * @since 15.0.0
 */
class PagesPerSessionSource extends AbstractSource
{
    protected $name       = 'pages_per_session';
    protected $expression = 'ROUND(AVG(sessions.total_views), 2)';
    protected $table      = 'sessions';
    protected $type       = 'float';
    protected $format     = 'number';
}
