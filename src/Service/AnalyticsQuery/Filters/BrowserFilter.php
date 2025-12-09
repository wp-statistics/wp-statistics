<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Browser filter - filters by browser name.
 *
 * @since 15.0.0
 */
class BrowserFilter extends AbstractFilter
{
    protected $name   = 'browser';
    protected $column = 'device_browsers.name';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'device_browsers',
        'alias' => 'device_browsers',
        'on'    => 'sessions.device_browser_id = device_browsers.ID',
    ];
}
