<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Group by event name.
 *
 * @since 15.0.0
 */
class EventNameGroupBy extends AbstractGroupBy
{
    protected $name        = 'event_name';
    protected $column      = 'events.event_name';
    protected $alias       = 'event_name';
    protected $groupBy     = 'events.event_name';
    protected $requirement = 'events';
}
