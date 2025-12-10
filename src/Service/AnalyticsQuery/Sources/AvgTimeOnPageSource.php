<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Average time on page source.
 *
 * @since 15.0.0
 */
class AvgTimeOnPageSource extends AbstractSource
{
    protected $name       = 'avg_time_on_page';
    protected $expression = 'ROUND(AVG(views.duration), 0)';
    protected $table      = 'views';
    protected $type       = 'integer';
    protected $format     = 'duration';
}
