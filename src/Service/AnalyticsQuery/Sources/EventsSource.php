<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Events source - counts total events.
 *
 * @since 15.0.0
 */
class EventsSource extends AbstractSource
{
    protected $name       = 'events';
    protected $expression = 'COUNT(events.ID)';
    protected $table      = 'events';
    protected $type       = 'integer';
    protected $format     = 'number';
}
