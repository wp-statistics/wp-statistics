<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * View completions source - counts distinct sessions with page views.
 *
 * Provides per-session deduplication: multiple views of the same page in
 * one session count as one completion. Used for page-view goal tracking.
 *
 * @since 15.1.0
 */
class ViewCompletionsSource extends AbstractSource
{
    protected $name       = 'view_completions';
    protected $expression = 'COUNT(DISTINCT views.session_id)';
    protected $table      = 'views';
    protected $type       = 'integer';
    protected $format     = 'number';
}
