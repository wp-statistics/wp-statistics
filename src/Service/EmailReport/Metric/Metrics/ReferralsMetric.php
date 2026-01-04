<?php

namespace WP_Statistics\Service\EmailReport\Metric\Metrics;

use WP_Statistics\Service\EmailReport\Metric\AbstractMetric;

/**
 * Referrals Metric
 *
 * Referral traffic count metric.
 *
 * @package WP_Statistics\Service\EmailReport\Metric\Metrics
 * @since 15.0.0
 */
class ReferralsMetric extends AbstractMetric
{
    protected string $type = 'referrals';
    protected string $name = 'Referrals';
    protected string $description = 'Visitors from referrals';
    protected string $icon = 'admin-links';

    /**
     * @inheritDoc
     */
    public function calculate(string $period): array
    {
        global $wpdb;

        $dateRange = $this->getDateRange($period);
        $sessionsTable = $wpdb->prefix . 'statistics_sessions';

        // Current period - count sessions with referrers (referrer_id IS NOT NULL)
        $current = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$sessionsTable}
            WHERE DATE(started_at) BETWEEN %s AND %s
            AND referrer_id IS NOT NULL",
            $dateRange['start_date'],
            $dateRange['end_date']
        ));

        // Previous period
        $previous = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$sessionsTable}
            WHERE DATE(started_at) BETWEEN %s AND %s
            AND referrer_id IS NOT NULL",
            $dateRange['previous_start_date'],
            $dateRange['previous_end_date']
        ));

        $change = $this->calculateChange($current, $previous);

        return [
            'type' => $this->type,
            'label' => __('Referrals', 'wp-statistics'),
            'value' => $current,
            'formatted' => $this->formatNumber($current),
            'previous' => $previous,
            'previousFormatted' => $this->formatNumber($previous),
            'change' => $change,
            'icon' => $this->icon,
        ];
    }
}
