/**
 * Hook for transforming ChartFormatter API responses to LineChart format.
 *
 * Provides memoized transformation of chart data with consistent:
 * - Null handling for previous period gaps
 * - Key transformation for metric names
 * - Total calculations
 * - Metric building with values
 */

import { useMemo } from 'react'

import {
  buildChartMetrics,
  calculateChartTotals,
  hasPreviousPeriodData,
  transformChartResponse,
} from '@/lib/chart-utils'
import type {
  ChartApiResponse,
  ChartTotals,
  LineChartDataPoint,
  LineChartMetric,
  UseChartDataOptions,
  UseChartDataResult,
} from '@/types/chart'

/**
 * Transform ChartFormatter API response to LineChart-ready format.
 *
 * This hook consolidates the common pattern of transforming chart API responses
 * that was previously duplicated across 10+ report pages.
 *
 * @param response - Raw API response from ChartFormatter (or null/undefined)
 * @param options - Configuration for transformation and metrics
 * @returns Transformed data, metrics with totals, and helper flags
 *
 * @example
 * ```tsx
 * function MyReport() {
 *   const { isCompareEnabled, apiDateParams } = useGlobalFilters()
 *   const { data: response } = useQuery(getChartQueryOptions(apiDateParams))
 *
 *   const { data, metrics } = useChartData(response?.data?.items?.chart, {
 *     metrics: [
 *       { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
 *       { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
 *     ],
 *     showPreviousValues: isCompareEnabled,
 *     preserveNull: true, // For line gaps when PP is shorter
 *   })
 *
 *   return (
 *     <LineChart
 *       data={data}
 *       metrics={metrics}
 *       showPreviousPeriod={isCompareEnabled}
 *       compareDateTo={apiDateParams.previous_date_to}
 *       dateTo={apiDateParams.date_to}
 *     />
 *   )
 * }
 * ```
 */
export function useChartData(
  response: ChartApiResponse | undefined | null,
  options: UseChartDataOptions
): UseChartDataResult {
  const { metrics: metricConfigs, showPreviousValues = false, preserveNull = false, keyMapping = {} } = options

  // Extract metric keys for total calculation
  const metricKeys = useMemo(() => metricConfigs.map((m) => m.key), [metricConfigs])

  // Transform response to LineChart data points
  const data = useMemo<LineChartDataPoint[]>(
    () => transformChartResponse(response, { preserveNull, keyMapping }),
    [response, preserveNull, keyMapping]
  )

  // Calculate totals for all metrics
  const totals = useMemo<ChartTotals>(() => calculateChartTotals(response, metricKeys), [response, metricKeys])

  // Build metrics array with values
  const metrics = useMemo<LineChartMetric[]>(
    () => buildChartMetrics(totals, metricConfigs, showPreviousValues),
    [totals, metricConfigs, showPreviousValues]
  )

  // Check for previous period data
  const hasPreviousPeriod = useMemo(() => hasPreviousPeriodData(response), [response])

  return {
    data,
    metrics,
    totals,
    hasPreviousPeriod,
  }
}
