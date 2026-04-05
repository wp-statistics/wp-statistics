<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Group by event target URL (extracted from events.event_data JSON).
 *
 * EventTracker stores target URL under the short key 'tu'.
 *
 * @since 15.0.0
 */
class EventTargetUrlGroupBy extends AbstractGroupBy
{
    protected $name        = 'event_target_url';
    protected $column      = "JSON_UNQUOTE(JSON_EXTRACT(events.event_data, '$.tu'))";
    protected $alias       = 'target_url';
    protected $groupBy     = "JSON_UNQUOTE(JSON_EXTRACT(events.event_data, '$.tu'))";
    protected $requirement = 'events';
    protected $filter      = "JSON_UNQUOTE(JSON_EXTRACT(events.event_data, '$.tu')) IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(events.event_data, '$.tu')) != ''";
}
