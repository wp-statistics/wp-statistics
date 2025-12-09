<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Browser version ID filter - filters by browser version ID.
 *
 * @since 15.0.0
 */
class BrowserVersionIdFilter extends AbstractFilter
{
    protected $name               = 'browser_version_id';
    protected $column             = 'sessions.device_browser_version_id';
    protected $type               = 'integer';
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];
}
