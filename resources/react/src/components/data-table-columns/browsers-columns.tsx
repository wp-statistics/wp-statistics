/**
 * Column definitions for the Browsers data table.
 * Device analytics grouped by browser.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { ChevronDown, ChevronRight } from 'lucide-react'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { NumericCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import type { BrowserRecord } from '@/services/devices/get-browsers'

/**
 * Context identifier for user preferences
 */
export const BROWSERS_CONTEXT = 'browsers'

/**
 * Columns that support PP comparison display
 */
export const BROWSERS_COMPARABLE_COLUMNS: string[] = ['visitors']

/**
 * Columns that show PP comparison by default
 */
export const BROWSERS_DEFAULT_COMPARISON_COLUMNS: string[] = ['visitors']

/**
 * Browser data interface for the table
 */
export interface BrowserData {
  id: string
  browserName: string
  browserId: number | string
  visitors: number
  previousVisitors?: number
  percentOfTotal: number
}

/**
 * Transform API response to BrowserData interface
 */
export function transformBrowserData(record: BrowserRecord, totalVisitors: number): BrowserData {
  const visitors = Number(record.visitors) || 0
  const browserName = record.browser_name || 'Unknown'

  return {
    id: `browser-${browserName}`,
    browserName,
    browserId: record.browser_id,
    visitors,
    previousVisitors:
      record.previous?.visitors !== undefined ? Number(record.previous.visitors) : undefined,
    percentOfTotal: totalVisitors > 0 ? (visitors / totalVisitors) * 100 : 0,
  }
}

/**
 * Options for creating Browsers columns
 */
export interface BrowsersColumnsOptions {
  comparisonLabel?: string
  comparisonColumns?: string[]
}

/**
 * Create column definitions for the Browsers table
 */
export function createBrowsersColumns(options: BrowsersColumnsOptions): ColumnDef<BrowserData>[] {
  const { comparisonLabel, comparisonColumns = BROWSERS_DEFAULT_COMPARISON_COLUMNS } = options

  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  return [
    {
      accessorKey: 'browserName',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: 200,
      cell: ({ row }) => {
        return (
          <div className="flex items-center gap-2">
            {row.getCanExpand() ? (
              <button
                onClick={(e) => {
                  e.stopPropagation()
                  row.toggleExpanded()
                }}
                className="flex items-center justify-center w-5 h-5 shrink-0 text-neutral-400 hover:text-neutral-600"
              >
                {row.getIsExpanded() ? (
                  <ChevronDown className="h-3.5 w-3.5" />
                ) : (
                  <ChevronRight className="h-3.5 w-3.5" />
                )}
              </button>
            ) : (
              <span className="w-5 shrink-0" />
            )}
            <span className="truncate text-xs font-medium text-neutral-700">{row.original.browserName}</span>
          </div>
        )
      },
      enableSorting: false,
      meta: {
        title: __('Browser', 'wp-statistics'),
        priority: 'primary' as const,
        cardPosition: 'header' as const,
      },
    },
    {
      accessorKey: 'visitors',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.visitors}
          previousValue={showComparison('visitors') ? row.original.previousVisitors : undefined}
          comparisonLabel={comparisonLabel}
        />
      ),
      meta: {
        title: __('Visitors', 'wp-statistics'),
        priority: 'primary' as const,
        cardPosition: 'body' as const,
        isComparable: true,
        showComparison: showComparison('visitors'),
      },
    },
    {
      accessorKey: 'percentOfTotal',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: 80,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.percentOfTotal}
          decimals={1}
          suffix="%"
        />
      ),
      meta: {
        title: __('% of Total', 'wp-statistics'),
        priority: 'secondary' as const,
      },
    },
  ]
}
