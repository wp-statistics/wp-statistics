<?php

namespace WP_Statistics\Service\EmailReport\Metric;

/**
 * Abstract Metric
 *
 * Base class for email metrics.
 *
 * @package WP_Statistics\Service\EmailReport\Metric
 * @since 15.0.0
 */
abstract class AbstractMetric implements MetricInterface
{
    /**
     * Metric type identifier
     *
     * @var string
     */
    protected string $type = '';

    /**
     * Metric display name
     *
     * @var string
     */
    protected string $name = '';

    /**
     * Metric description
     *
     * @var string
     */
    protected string $description = '';

    /**
     * Metric icon (dashicon name)
     *
     * @var string
     */
    protected string $icon = 'chart-line';

    /**
     * Get metric type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get metric name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get metric description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get metric icon
     *
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Get date range for a period
     *
     * @param string $period Period type
     * @return array
     */
    protected function getDateRange(string $period): array
    {
        $now = new \DateTime('now', wp_timezone());
        $end = clone $now;
        $start = clone $now;
        $previousEnd = clone $now;
        $previousStart = clone $now;

        switch ($period) {
            case 'daily':
                $start->modify('-1 day');
                $previousEnd->modify('-1 day');
                $previousStart->modify('-2 days');
                break;

            case 'weekly':
                $start->modify('-7 days');
                $previousEnd->modify('-7 days');
                $previousStart->modify('-14 days');
                break;

            case 'biweekly':
                $start->modify('-14 days');
                $previousEnd->modify('-14 days');
                $previousStart->modify('-28 days');
                break;

            case 'monthly':
                $start->modify('-30 days');
                $previousEnd->modify('-30 days');
                $previousStart->modify('-60 days');
                break;

            default:
                $start->modify('-7 days');
                $previousEnd->modify('-7 days');
                $previousStart->modify('-14 days');
        }

        return [
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'previous_start_date' => $previousStart->format('Y-m-d'),
            'previous_end_date' => $previousEnd->format('Y-m-d'),
        ];
    }

    /**
     * Calculate percentage change
     *
     * @param int|float $current Current value
     * @param int|float $previous Previous value
     * @return array
     */
    protected function calculateChange($current, $previous): array
    {
        if ($previous == 0) {
            if ($current > 0) {
                return ['value' => 100, 'direction' => 'up', 'formatted' => '+100%'];
            }
            return ['value' => 0, 'direction' => 'neutral', 'formatted' => '0%'];
        }

        $change = (($current - $previous) / $previous) * 100;
        $direction = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral');
        $prefix = $change > 0 ? '+' : '';

        return [
            'value' => abs(round($change, 1)),
            'direction' => $direction,
            'formatted' => $prefix . round($change, 1) . '%',
        ];
    }

    /**
     * Format number for display
     *
     * @param int|float $number Number to format
     * @return string
     */
    protected function formatNumber($number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        }

        if ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }

        return number_format_i18n($number);
    }

    /**
     * Convert to array for React
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'icon' => $this->getIcon(),
        ];
    }
}
