<?php

namespace WP_Statistics\Service\EmailReport\Metric;

use WP_Statistics\Service\EmailReport\Metric\Metrics\VisitorsMetric;
use WP_Statistics\Service\EmailReport\Metric\Metrics\ViewsMetric;
use WP_Statistics\Service\EmailReport\Metric\Metrics\SessionsMetric;
use WP_Statistics\Service\EmailReport\Metric\Metrics\ReferralsMetric;
use WP_Statistics\Service\EmailReport\Metric\Metrics\ContentsMetric;

/**
 * Metric Registry
 *
 * Manages registration and retrieval of email metrics.
 *
 * @package WP_Statistics\Service\EmailReport\Metric
 * @since 15.0.0
 */
class MetricRegistry
{
    /**
     * Registered metrics
     *
     * @var array<string, MetricInterface>
     */
    private array $metrics = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->registerDefaultMetrics();
    }

    /**
     * Register default metrics
     *
     * @return void
     */
    private function registerDefaultMetrics(): void
    {
        $this->register(new VisitorsMetric());
        $this->register(new ViewsMetric());
        $this->register(new SessionsMetric());
        $this->register(new ReferralsMetric());
        $this->register(new ContentsMetric());

        /**
         * Allow add-ons to register custom metrics
         *
         * @param MetricRegistry $registry Metric registry instance
         */
        do_action('wp_statistics_email_report_register_metrics', $this);
    }

    /**
     * Register a metric
     *
     * @param MetricInterface $metric Metric instance
     * @return void
     */
    public function register(MetricInterface $metric): void
    {
        $this->metrics[$metric->getType()] = $metric;
    }

    /**
     * Unregister a metric
     *
     * @param string $type Metric type
     * @return void
     */
    public function unregister(string $type): void
    {
        unset($this->metrics[$type]);
    }

    /**
     * Check if a metric is registered
     *
     * @param string $type Metric type
     * @return bool
     */
    public function has(string $type): bool
    {
        return isset($this->metrics[$type]);
    }

    /**
     * Get a metric by type
     *
     * @param string $type Metric type
     * @return MetricInterface|null
     */
    public function get(string $type): ?MetricInterface
    {
        return $this->metrics[$type] ?? null;
    }

    /**
     * Get all registered metrics
     *
     * @return array<string, MetricInterface>
     */
    public function getAll(): array
    {
        return $this->metrics;
    }

    /**
     * Get available metrics for the email builder UI
     *
     * @return array
     */
    public function getAvailableMetrics(): array
    {
        $available = [];

        foreach ($this->metrics as $metric) {
            $available[] = $metric->toArray();
        }

        return $available;
    }
}
