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
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { formatChartValue, transformChartResponse } from '@/lib/chart-utils'
import type { LineChartDataPoint, LineChartMetric } from '@/types/chart'

const CHART_COLORS = [
  'var(--chart-1)',
  'var(--chart-2)',
  'var(--chart-3)',
  'var(--chart-4)',
]

interface ChartDataset {
  key: string
  label: string
  data: (number | null)[]
  comparison?: boolean
}

interface ChartResponse {
  datasets: ChartDataset[]
  labels: string[]
  previousLabels?: string[]
}

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

  const chartResponse = useMemo(() => {
    const resp = rawResponse as { data?: { _batchItems?: Record<string, ChartResponse> } } | undefined
    return resp?.data?._batchItems?.[chartConfig.queryId] as ChartResponse | undefined
  }, [rawResponse, chartConfig.queryId])

  const { chartData, chartMetrics } = useMemo(() => {
    if (!chartResponse?.datasets) {
      return { chartData: [] as LineChartDataPoint[], chartMetrics: [] as LineChartMetric[] }
    }

    const currentDatasets = chartResponse.datasets.filter((ds) => !ds.comparison)
    const previousDatasets = chartResponse.datasets.filter((ds) => ds.comparison)

    const metrics: LineChartMetric[] = currentDatasets.map((ds, index) => {
      const total = ds.data.reduce((sum, v) => sum + (v !== null ? Number(v) : 0), 0)

      const metric: LineChartMetric = {
        key: ds.key,
        label: ds.label,
        color: CHART_COLORS[index % CHART_COLORS.length],
        enabled: true,
        value: formatChartValue(total),
      }

      if (ds.key === compareMetricKey && isCompareEnabled) {
        const prevDataset = previousDatasets.find((p) => p.key === `${compareMetricKey}_previous`)
        if (prevDataset?.data) {
          const prevTotal = prevDataset.data.reduce((sum, v) => sum + (v !== null ? Number(v) : 0), 0)
          metric.previousValue = formatChartValue(prevTotal)
        }
      }

      return metric
    })

    const data = transformChartResponse(chartResponse, { preserveNull: true })

    return { chartData: data, chartMetrics: metrics }
  }, [chartResponse, isCompareEnabled, compareMetricKey])

  if (!chartResponse?.datasets) return null

  return (
    <div className="mb-3">
      <LineChart
        title={chartConfig.title}
        data={chartData}
        metrics={chartMetrics}
        showPreviousPeriod={isCompareEnabled}
        compareDateTo={apiDateParams.previous_date_to}
        dateTo={apiDateParams.date_to}
        borderless
      />
    </div>
  )
}
