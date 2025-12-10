<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Average session duration source.
 *
 * @since 15.0.0
 */
class AvgSessionDurationSource extends AbstractSource
{
    protected $name       = 'avg_session_duration';
    protected $expression = 'ROUND(AVG(sessions.duration), 0)';
    protected $table      = 'sessions';
    protected $type       = 'integer';
    protected $format     = 'duration';
}
