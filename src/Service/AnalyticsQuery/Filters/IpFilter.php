<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * IP filter - filters by visitor IP address.
 *
 * @since 15.0.0
 */
class IpFilter extends AbstractFilter
{
    protected $name               = 'ip';
    protected $column             = 'sessions.ip';
    protected $type               = 'string';
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'];
}
