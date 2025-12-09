<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Timezone filter - filters by visitor timezone.
 *
 * @since 15.0.0
 */
class TimezoneFilter extends AbstractFilter
{
    protected $name   = 'timezone';
    protected $column = 'timezones.name';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'timezones',
        'alias' => 'timezones',
        'on'    => 'sessions.timezone_id = timezones.ID',
    ];
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'];
}
