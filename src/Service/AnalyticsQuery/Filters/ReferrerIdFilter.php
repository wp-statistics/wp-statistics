<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer ID filter - filters by referrer ID.
 *
 * @since 15.0.0
 */
class ReferrerIdFilter extends AbstractFilter
{
    protected $name               = 'referrer_id';
    protected $column             = 'sessions.referrer_id';
    protected $type               = 'integer';
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];
}
