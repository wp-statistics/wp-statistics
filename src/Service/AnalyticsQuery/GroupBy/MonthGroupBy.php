<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Month group by - groups by month.
 *
 * @since 15.0.0
 */
class MonthGroupBy extends AbstractGroupBy
{
    protected $name         = 'month';
    protected $column       = "DATE_FORMAT(sessions.started_at, '%Y-%m')";
    protected $alias        = 'month';
    protected $extraColumns = [
        "DATE_FORMAT(sessions.started_at, '%M %Y') AS month_name",
    ];
    protected $groupBy      = "DATE_FORMAT(sessions.started_at, '%Y-%m')";
    protected $order        = 'ASC';
}
