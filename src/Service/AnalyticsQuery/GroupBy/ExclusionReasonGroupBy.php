<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Exclusion reason group by - groups by exclusion reason.
 *
 * @since 15.0.0
 */
class ExclusionReasonGroupBy extends AbstractGroupBy
{
    protected $name        = 'exclusion_reason';
    protected $column      = 'exclusions.reason';
    protected $alias       = 'reason';
    protected $groupBy     = 'exclusions.reason';
    protected $order       = 'DESC';
    protected $requirement = 'exclusions';
}
