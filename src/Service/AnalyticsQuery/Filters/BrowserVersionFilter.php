<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Browser version filter - filters by browser version.
 *
 * @since 15.0.0
 */
class BrowserVersionFilter extends AbstractFilter
{
    protected $name   = 'browser_version';
    protected $column = 'device_browser_versions.version';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'device_browser_versions',
        'alias' => 'device_browser_versions',
        'on'    => 'sessions.device_browser_version_id = device_browser_versions.ID',
    ];
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'];
}
