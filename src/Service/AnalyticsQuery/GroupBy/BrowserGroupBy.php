<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Browser group by - groups by browser.
 *
 * @since 15.0.0
 */
class BrowserGroupBy extends AbstractGroupBy
{
    protected $name         = 'browser';
    protected $column       = 'device_browsers.name';
    protected $alias        = 'browser_name';
    protected $extraColumns = [
        'device_browsers.ID AS browser_id',
        'device_browser_versions.version AS browser_version',
        'device_browser_versions.ID AS browser_version_id',
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
            'type'  => 'LEFT',
        ],
    ];
    protected $groupBy      = 'device_browsers.ID';
}
