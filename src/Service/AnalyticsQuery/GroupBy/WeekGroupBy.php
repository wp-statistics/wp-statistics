<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Week group by - groups by week.
 *
 * @since 15.0.0
 */
class WeekGroupBy extends AbstractGroupBy
{
    protected $name         = 'week';
    protected $column       = 'YEARWEEK(sessions.started_at, 1)';
    protected $alias        = 'week';
    protected $extraColumns = [
        "DATE(DATE_SUB(sessions.started_at, INTERVAL WEEKDAY(sessions.started_at) DAY)) AS week_start",
        "DATE(DATE_ADD(DATE_SUB(sessions.started_at, INTERVAL WEEKDAY(sessions.started_at) DAY), INTERVAL 6 DAY)) AS week_end",
    ];
    protected $groupBy      = 'YEARWEEK(sessions.started_at, 1)';
    protected $order        = 'ASC';
}
