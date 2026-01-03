<?php

namespace WP_Statistics\Service\EmailReport\Metric\Metrics;

use WP_Statistics\Service\EmailReport\Metric\AbstractMetric;

/**
 * Sessions Metric
 *
 * Total sessions count metric.
 *
 * @package WP_Statistics\Service\EmailReport\Metric\Metrics
 * @since 15.0.0
 */
class SessionsMetric extends AbstractMetric
{
    protected string $type = 'sessions';
    protected string $name = 'Sessions';
    protected string $description = 'Total browsing sessions';
    protected string $icon = 'clock';

    /**
     * @inheritDoc
     */
    public function calculate(string $period): array
    {
        global $wpdb;

        $dateRange = $this->getDateRange($period);
        $visitorsTable = $wpdb->prefix . 'statistics_visitors';

        // Current period - count visits (each visitor visit is a session)
        $current = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(hits) FROM {$visitorsTable} WHERE last_counter BETWEEN %s AND %s",
            $dateRange['start_date'],
            $dateRange['end_date']
        ));

        // Previous period
        $previous = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(hits) FROM {$visitorsTable} WHERE last_counter BETWEEN %s AND %s",
            $dateRange['previous_start_date'],
            $dateRange['previous_end_date']
        ));

        $change = $this->calculateChange($current, $previous);

        return [
            'type' => $this->type,
            'label' => __('Sessions', 'wp-statistics'),
            'value' => $current,
            'formatted' => $this->formatNumber($current),
            'previous' => $previous,
            'previousFormatted' => $this->formatNumber($previous),
            'change' => $change,
            'icon' => $this->icon,
        ];
    }
}
