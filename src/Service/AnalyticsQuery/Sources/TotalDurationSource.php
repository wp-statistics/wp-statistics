<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Total duration source.
 *
 * @since 15.0.0
 */
class TotalDurationSource extends AbstractSource
{
    protected $name       = 'total_duration';
    protected $expression = 'SUM(sessions.duration)';
    protected $table      = 'sessions';
    protected $type       = 'integer';
    protected $format     = 'duration';
}
