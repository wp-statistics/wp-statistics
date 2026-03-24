<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Event target URL filter - filters events by target URL (from event_data JSON).
 *
 * EventTracker stores the target URL under the short key 'tu'.
 *
 * @since 15.0.0
 */
class EventTargetUrlFilter extends AbstractFilter
{
    protected $name = 'event_target_url';

    protected $column = "JSON_UNQUOTE(JSON_EXTRACT(events.event_data, '$.tu'))";

    protected $type = 'string';

    protected $requirement = 'events';

    protected $inputType = 'text';

    protected $supportedOperators = ['is', 'is_not', 'contains', 'not_contains'];

    protected $groups = ['events'];

    public function getLabel(): string
    {
        return esc_html__('Event Target URL', 'wp-statistics');
    }
}
