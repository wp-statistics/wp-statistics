<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * OS filter - filters by operating system name.
 *
 * @since 15.0.0
 */
class OsFilter extends AbstractFilter
{
    protected $name   = 'os';
    protected $column = 'device_oss.name';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'device_oss',
        'alias' => 'device_oss',
        'on'    => 'sessions.device_os_id = device_oss.ID',
    ];
}
