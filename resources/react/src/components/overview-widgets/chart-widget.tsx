import { LineChart } from '@/components/custom/line-chart'
import { useChartData } from '@/hooks/use-chart-data'
import type { Timeframe } from '@/lib/response-helpers'

import type { BatchQueryResult } from './types'

export function ChartWidget({
  widget,
  queryResult,
  isCompareEnabled,
  timeframe,
  onTimeframeChange,
  loading,
  apiDateParams,
  headerRight,
}: {
  widget: PhpOverviewWidget
  queryResult: BatchQueryResult | undefined
  isCompareEnabled: boolean
  timeframe: Timeframe
  onTimeframeChange?: (tf: Timeframe) => void
  loading: boolean
  apiDateParams: { date_to: string; previous_date_to?: string }
  headerRight?: React.ReactNode
}) {
  const { data: chartData, metrics } = useChartData(queryResult, {
    metrics: widget.chartConfig!.metrics.map((m) => ({
      key: m.key,
      label: m.label,
      color: m.color,
      ...(m.type && { type: m.type }),
    })),
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  return (
    <LineChart
      className="h-full"
      title={widget.label}
      data={chartData}
      metrics={metrics}
      showPreviousPeriod={isCompareEnabled}
      timeframe={timeframe}
      onTimeframeChange={onTimeframeChange}
      loading={loading}
      dateTo={apiDateParams.date_to}
      compareDateTo={apiDateParams.previous_date_to}
      headerRight={headerRight}
    />
  )
}
