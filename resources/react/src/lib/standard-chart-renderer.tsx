/**
 * Standard Chart Renderer
 *
 * Factory function that creates a beforeTable slot component
 * for reports with chart-above-table layout.
 * Reads chart data from the batch response's chart sub-query.
 */

/* eslint-disable react-refresh/only-export-components -- This is a factory module, not a component file */
import { useMemo } from 'react'

import { LineChart } from '@/components/custom/line-chart'
import type { SlotRenderProps } from '@/components/report-page-renderer'
import { chartColors } from '@/constants/design-tokens'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { calculateChartTotals, formatChartValue, transformChartResponse } from '@/lib/chart-utils'
import { extractBatchItem } from '@/lib/response-helpers'
import type { ChartApiResponse, LineChartMetric } from '@/types/chart'

const CHART_COLORS = [chartColors.chart1, chartColors.chart2, chartColors.chart3, chartColors.chart4]

/**
 * Create a beforeTable slot function that renders a LineChart
 * from batch response chart data.
 */
export function createChartSlot(chartConfig: PhpChartConfig) {
  const compareMetricKey = chartConfig.compareMetricKey || 'total'

  return function ChartSlotWrapper(props: SlotRenderProps) {
    return <ChartSlotComponent {...props} chartConfig={chartConfig} compareMetricKey={compareMetricKey} />
  }
}

function ChartSlotComponent({
  rawResponse,
  chartConfig,
  compareMetricKey,
}: SlotRenderProps & { chartConfig: PhpChartConfig; compareMetricKey: string }) {
  const { isCompareEnabled, apiDateParams } = useGlobalFilters()

  const chartResponse = extractBatchItem<ChartApiResponse>(rawResponse, chartConfig.queryId)

  const { chartData, chartMetrics } = useMemo(() => {
    if (!chartResponse?.datasets) return { chartData: [], chartMetrics: [] }

    const currentDatasets = chartResponse.datasets.filter((ds) => !ds.comparison)
    const metricKeys = currentDatasets.map((ds) => ds.key)
    const totals = calculateChartTotals(chartResponse, metricKeys)

    const metrics: LineChartMetric[] = currentDatasets.map((ds, index) => ({
      key: ds.key,
      label: ds.label,
      color: CHART_COLORS[index % CHART_COLORS.length],
      enabled: true,
      value: formatChartValue(totals[ds.key]?.current ?? 0),
      ...(ds.key === compareMetricKey && isCompareEnabled && {
        previousValue: formatChartValue(totals[ds.key]?.previous ?? 0),
      }),
    }))

    return {
      chartData: transformChartResponse(chartResponse, { preserveNull: true }),
      chartMetrics: metrics,
    }
  }, [chartResponse, isCompareEnabled, compareMetricKey])

  if (!chartResponse?.datasets) return null

  return (
    <LineChart
      title={chartConfig.title}
      data={chartData}
      metrics={chartMetrics}
      showPreviousPeriod={isCompareEnabled}
      compareDateTo={apiDateParams.previous_date_to}
      dateTo={apiDateParams.date_to}
      borderless
    />
  )
}
