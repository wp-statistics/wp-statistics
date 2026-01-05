<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Exclusion date group by - groups by exclusion date.
 *
 * @since 15.0.0
 */
class ExclusionDateGroupBy extends AbstractGroupBy
{
    protected $name        = 'exclusion_date';
    protected $column      = 'exclusions.date';
    protected $alias       = 'date';
    protected $groupBy     = 'exclusions.date';
    protected $order       = 'ASC';
    protected $requirement = 'exclusions';
}
