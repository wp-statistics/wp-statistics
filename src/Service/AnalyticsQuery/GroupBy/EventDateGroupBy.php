<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Group by event date.
 *
 * Uses events.date directly instead of sessions.started_at,
 * so it works when events is the primary table without a session join.
 *
 * @since 15.0.0
 */
class EventDateGroupBy extends AbstractGroupBy
{
    protected $name        = 'event_date';
    protected $column      = 'DATE(events.date)';
    protected $alias       = 'date';
    protected $groupBy     = 'DATE(events.date)';
    protected $order       = 'ASC';
    protected $requirement = 'events';
}
