import { keepPreviousData, useQuery } from '@tanstack/react-query'
import type { Row } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'

import { NumericCell } from '@/components/data-table-columns'
import { clientRequest } from '@/lib/client-request'
import { WordPress } from '@/lib/wordpress'

interface ExpandableSubRowProps {
  row: Row<Record<string, unknown>>
  config: PhpExpandableRowsConfig
  apiDateParams: {
    date_from: string
    date_to: string
    previous_date_from?: string
    previous_date_to?: string
  }
}

interface SubQueryResponse {
  success: boolean
  items: Record<string, { data?: { rows?: Record<string, unknown>[] } }>
}

export function ExpandableSubRow({ row, config, apiDateParams }: ExpandableSubRowProps) {
  const parentRow = row.original

  // Build filters by replacing valueField references with actual parent row values
  const filters = config.subQuery.filters.map((f) => ({
    key: f.key,
    operator: f.operator,
    value: String(parentRow[f.valueField] || ''),
  }))

  const hasCompare = !!(apiDateParams.previous_date_from && apiDateParams.previous_date_to)

  const { data: response, isLoading } = useQuery({
    queryKey: [
      'expandable-sub-row',
      config.parentIdField,
      parentRow[config.parentIdField],
      apiDateParams.date_from,
      apiDateParams.date_to,
      apiDateParams.previous_date_from,
      apiDateParams.previous_date_to,
      hasCompare,
      config.subQuery.sources,
      config.subQuery.group_by,
      config.subQuery.columns,
      filters,
      config.subQuery.order_by,
      config.subQuery.order,
      config.subQuery.per_page,
    ],
    queryFn: () =>
      clientRequest.post<SubQueryResponse>(
        '',
        {
          date_from: apiDateParams.date_from,
          date_to: apiDateParams.date_to,
          compare: hasCompare,
          ...(hasCompare && {
            previous_date_from: apiDateParams.previous_date_from,
            previous_date_to: apiDateParams.previous_date_to,
          }),
          queries: [
            {
              id: 'sub_data',
              sources: config.subQuery.sources,
              group_by: config.subQuery.group_by,
              ...(config.subQuery.columns && { columns: config.subQuery.columns }),
              filters,
              order_by: config.subQuery.order_by,
              order: config.subQuery.order,
              per_page: config.subQuery.per_page || 50,
              format: 'table',
              compare: hasCompare,
            },
          ],
        },
        { params: { action: WordPress.getInstance().getAnalyticsAction() } }
      ),
    placeholderData: keepPreviousData,
  })

  const rows = response?.data?.items?.sub_data?.data?.rows || []

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-4">
        <Loader2 className="h-4 w-4 animate-spin text-neutral-400" />
      </div>
    )
  }

  if (rows.length === 0) {
    return (
      <div className="pl-14 py-3 text-xs text-neutral-500">
        {config.emptyMessage || __('No data available', 'wp-statistics')}
      </div>
    )
  }

  return (
    <div className="border-t border-neutral-200">
      <table className="w-full">
        <thead>
          <tr className="bg-neutral-100/60">
            {config.subColumns.map((col, i) => (
              <td
                key={col.key}
                className={`py-1.5 text-xs font-medium text-neutral-500 ${
                  i === 0 ? 'pl-14' : col.type === 'numeric' ? 'text-right pr-4' : ''
                }`}
                style={col.type === 'numeric' ? { width: 120 } : undefined}
              >
                {col.title}
              </td>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((item, idx) => (
            <tr
              key={idx}
              className="border-t border-neutral-100 bg-neutral-50 hover:bg-neutral-100/50"
            >
              {config.subColumns.map((col, i) => (
                <td
                  key={col.key}
                  className={`py-1.5 ${
                    i === 0 ? 'pl-14 text-xs font-medium text-neutral-700' : col.type === 'numeric' ? 'pr-4' : ''
                  }`}
                  style={col.type === 'numeric' ? { width: 120 } : undefined}
                >
                  {col.type === 'numeric' ? (
                    <NumericCell
                      value={Number(item[col.key]) || 0}
                      previousValue={
                        col.comparable && col.previousKey
                          ? Number((item.previous as Record<string, unknown>)?.[col.previousKey.replace('previous.', '')])
                          : undefined
                      }
                    />
                  ) : (
                    String(item[col.key] || __('Unknown', 'wp-statistics'))
                  )}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
