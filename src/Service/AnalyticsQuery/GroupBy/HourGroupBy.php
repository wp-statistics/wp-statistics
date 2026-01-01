<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Hour group by - groups by hour.
 *
 * @since 15.0.0
 */
class HourGroupBy extends AbstractGroupBy
{
    protected $name         = 'hour';
    protected $column       = 'HOUR(sessions.started_at)';
    protected $alias        = 'hour';
    protected $extraColumns = [
        "CONCAT(LPAD(HOUR(sessions.started_at), 2, '0'), ':00') AS hour_label",
    ];
    protected $groupBy      = 'HOUR(sessions.started_at)';
    protected $order        = 'ASC';
}
