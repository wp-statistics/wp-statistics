<?php

namespace WP_Statistics\Service\EmailReport\Metric;

/**
 * Metric Interface
 *
 * Defines the contract for email metrics (KPIs).
 *
 * @package WP_Statistics\Service\EmailReport\Metric
 * @since 15.0.0
 */
interface MetricInterface
{
    /**
     * Get metric type identifier
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get metric display name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get metric description
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get metric icon
     *
     * @return string
     */
    public function getIcon(): string;

    /**
     * Calculate metric for given period
     *
     * @param string $period Report period
     * @return array ['value' => int, 'formatted' => string, 'change' => array, 'label' => string]
     */
    public function calculate(string $period): array;
}
