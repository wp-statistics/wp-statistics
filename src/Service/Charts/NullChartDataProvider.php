<?php

namespace WP_Statistics\Service\Charts;

/**
 * Null-safe chart data provider returned by the deprecated ChartDataProviderFactory.
 *
 * Returns empty chart data for any method call, preventing fatal errors
 * in addons that still reference the old Charts API.
 *
 * @deprecated 15.0.0
 */
class NullChartDataProvider extends AbstractChartDataProvider
{
    public function getData()
    {
        return [
            'data' => [
                'labels'   => [],
                'datasets' => [],
            ]
        ];
    }

    /**
     * Catch-all for any chart-specific method (e.g. getOsData, getBrowserData, getSourceData).
     */
    public function __call($name, $arguments)
    {
        return [
            'data' => [
                'labels'   => [],
                'datasets' => [],
            ]
        ];
    }
}
