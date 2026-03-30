<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Event completions source - counts distinct sessions with events.
 *
 * Provides per-session deduplication: multiple events in the same session
 * count as one completion. Used for goal conversion tracking.
 *
 * @since 15.1.0
 */
class EventCompletionsSource extends AbstractSource
{
    protected $name       = 'event_completions';
    protected $expression = 'COUNT(DISTINCT events.session_id)';
    protected $table      = 'events';
    protected $type       = 'integer';
    protected $format     = 'number';
}
