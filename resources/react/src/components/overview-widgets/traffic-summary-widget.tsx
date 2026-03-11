import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { SimpleTable, type SimpleTableColumn } from '@/components/custom/simple-table'
import { NumericCell } from '@/components/data-table-columns'
import type { FixedDatePeriod } from '@/lib/fixed-date-ranges'

import type { TrafficSummaryPeriodResponse, TrafficSummaryRow } from './types'

export function TrafficSummaryWidget({
  widget,
  periods,
  queries,
}: {
  widget: PhpOverviewWidget
  periods: FixedDatePeriod[]
  queries: { data: unknown; isLoading: boolean }[]
}) {
  const config = widget.trafficSummaryConfig!
  const isLoading = queries.some((q) => q.isLoading)

  const columns = useMemo(
    (): SimpleTableColumn<TrafficSummaryRow>[] => [
      {
        key: 'period',
        header: __('Time Period', 'wp-statistics'),
        cell: (row) => <span className="font-medium">{row.label}</span>,
      },
      ...config.metrics.map((metric) => ({
        key: metric.key,
        header: metric.label,
        align: 'right' as const,
        cell: (row: TrafficSummaryRow) => (
          <NumericCell
            value={row.values[metric.key] || 0}
            previousValue={row.previousValues?.[metric.key]}
            comparisonLabel={row.comparisonLabel}
          />
        ),
      })),
    ],
    [config.metrics]
  )

  const data = useMemo(
    (): TrafficSummaryRow[] =>
      periods.map((period, i) => {
        const response = queries[i]?.data as
          | { data?: TrafficSummaryPeriodResponse }
          | undefined
        const totals = response?.data?.items?.traffic_summary?.totals
        const values: Record<string, number> = {}
        const previousValues: Record<string, number> = {}

        for (const metric of config.metrics) {
          const val = totals?.[metric.key]
          values[metric.key] = Number(val?.current) || 0
          if (period.id !== 'total' && val?.previous !== undefined) {
            previousValues[metric.key] = Number(val.previous) || 0
          }
        }

        return {
          label: period.label,
          values,
          previousValues:
            Object.keys(previousValues).length > 0 ? previousValues : undefined,
          comparisonLabel: period.comparisonLabel,
        }
      }),

    [periods, queries, config.metrics]
  )

  return (
    <SimpleTable title={widget.label} columns={columns} data={data} isLoading={isLoading} />
  )
}
