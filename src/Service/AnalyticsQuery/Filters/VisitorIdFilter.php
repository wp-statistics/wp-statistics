<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Visitor ID filter - filters by visitor ID.
 *
 * @since 15.0.0
 */
class VisitorIdFilter extends AbstractFilter
{
    protected $name               = 'visitor_id';
    protected $column             = 'sessions.visitor_id';
    protected $type               = 'integer';
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];
}
