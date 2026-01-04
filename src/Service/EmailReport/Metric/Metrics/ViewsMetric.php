<?php

namespace WP_Statistics\Service\EmailReport\Metric\Metrics;

use WP_Statistics\Service\EmailReport\Metric\AbstractMetric;

/**
 * Views Metric
 *
 * Page views count metric.
 *
 * @package WP_Statistics\Service\EmailReport\Metric\Metrics
 * @since 15.0.0
 */
class ViewsMetric extends AbstractMetric
{
    protected string $type = 'views';
    protected string $name = 'Views';
    protected string $description = 'Total page views';
    protected string $icon = 'visibility';

    /**
     * @inheritDoc
     */
    public function calculate(string $period): array
    {
        global $wpdb;

        $dateRange = $this->getDateRange($period);
        $summaryTotalsTable = $wpdb->prefix . 'statistics_summary_totals';

        // Current period - use summary_totals for aggregated view counts
        $current = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(views), 0) FROM {$summaryTotalsTable} WHERE date BETWEEN %s AND %s",
            $dateRange['start_date'],
            $dateRange['end_date']
        ));

        // Previous period
        $previous = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(views), 0) FROM {$summaryTotalsTable} WHERE date BETWEEN %s AND %s",
            $dateRange['previous_start_date'],
            $dateRange['previous_end_date']
        ));

        $change = $this->calculateChange($current, $previous);

        return [
            'type' => $this->type,
            'label' => __('Views', 'wp-statistics'),
            'value' => $current,
            'formatted' => $this->formatNumber($current),
            'previous' => $previous,
            'previousFormatted' => $this->formatNumber($previous),
            'change' => $change,
            'icon' => $this->icon,
        ];
    }
}
