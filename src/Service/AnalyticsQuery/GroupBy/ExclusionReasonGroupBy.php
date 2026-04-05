<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

use WP_Statistics\Service\Tracking\Core\Exclusions;

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

    protected $postProcessedColumns = ['reason_name'];

    /**
     * {@inheritdoc}
     */
    public function postProcess(array $rows, \wpdb $wpdb): array
    {
        $labels = Exclusions::getReasonLabels();

        foreach ($rows as &$row) {
            $reason             = $row['reason'] ?? '';
            $row['reason_name'] = $labels[$reason] ?? ucfirst(str_replace('_', ' ', $reason));
        }

        return $rows;
    }
}
