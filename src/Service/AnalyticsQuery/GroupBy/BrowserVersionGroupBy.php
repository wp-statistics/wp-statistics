<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Browser version group by - groups by browser version.
 *
 * @since 15.0.0
 */
class BrowserVersionGroupBy extends AbstractGroupBy
{
    protected $name         = 'browser_version';
    protected $column       = 'device_browser_versions.version';
    protected $alias        = 'browser_version';
    protected $extraColumns = [
        'device_browser_versions.ID AS browser_version_id',
        'device_browsers.name AS browser_name',
        'device_browsers.ID AS browser_id',
    ];
    protected $joins        = [
        [
            'table' => 'device_browsers',
            'alias' => 'device_browsers',
            'on'    => 'sessions.device_browser_id = device_browsers.ID',
            'type'  => 'INNER',
        ],
        [
            'table' => 'device_browser_versions',
            'alias' => 'device_browser_versions',
            'on'    => 'sessions.device_browser_version_id = device_browser_versions.ID',
            'type'  => 'INNER',
        ],
    ];
    protected $groupBy      = 'device_browser_versions.ID';
}
