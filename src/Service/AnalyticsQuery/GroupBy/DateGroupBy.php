<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Date group by - groups by day.
 *
 * @since 15.0.0
 */
class DateGroupBy extends AbstractGroupBy
{
    protected $name    = 'date';
    protected $column  = 'DATE(sessions.started_at)';
    protected $alias   = 'date';
    protected $groupBy = 'DATE(sessions.started_at)';
    protected $order   = 'ASC';
}
