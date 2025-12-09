<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Resource ID filter - filters by resource ID.
 *
 * @since 15.0.0
 */
class ResourceIdFilter extends AbstractFilter
{
    protected $name               = 'resource_id';
    protected $column             = 'views.resource_id';
    protected $type               = 'integer';
    protected $requirement        = 'views';
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];
}
