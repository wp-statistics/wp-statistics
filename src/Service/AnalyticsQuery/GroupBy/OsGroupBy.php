<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * OS group by - groups by operating system.
 *
 * @since 15.0.0
 */
class OsGroupBy extends AbstractGroupBy
{
    protected $name    = 'os';
    protected $column  = 'device_oss.name';
    protected $alias   = 'os';
    protected $joins   = [
        'table' => 'device_oss',
        'alias' => 'device_oss',
        'on'    => 'sessions.device_os_id = device_oss.ID',
        'type'  => 'INNER',
    ];
    protected $groupBy = 'device_oss.ID';
}
