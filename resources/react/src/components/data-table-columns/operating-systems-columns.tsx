/**
 * Column definitions for the Operating Systems data table.
 * Device analytics grouped by operating system.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { NumericCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import type { OsRecord } from '@/services/devices/get-operating-systems'

/**
 * Context identifier for user preferences
 */
export const OS_CONTEXT = 'operating-systems'

/**
 * Columns that support PP comparison display
 */
export const OS_COMPARABLE_COLUMNS: string[] = ['visitors']

/**
 * Columns that show PP comparison by default
 */
export const OS_DEFAULT_COMPARISON_COLUMNS: string[] = ['visitors']

/**
 * OS data interface for the table
 */
export interface OsData {
  id: string
  osName: string
  osId: number | string
  visitors: number
  previousVisitors?: number
  percentOfTotal: number
}

/**
 * Transform API response to OsData interface
 */
export function transformOsData(record: OsRecord, totalVisitors: number): OsData {
  const visitors = Number(record.visitors) || 0
  const osName = record.os_name || 'Unknown'

  return {
    id: `os-${osName}`,
    osName,
    osId: record.os_id,
    visitors,
    previousVisitors:
      record.previous?.visitors !== undefined ? Number(record.previous.visitors) : undefined,
    percentOfTotal: totalVisitors > 0 ? (visitors / totalVisitors) * 100 : 0,
  }
}

/**
 * Options for creating OS columns
 */
export interface OsColumnsOptions {
  comparisonLabel?: string
  comparisonColumns?: string[]
}

/**
 * Create column definitions for the Operating Systems table
 */
export function createOsColumns(options: OsColumnsOptions): ColumnDef<OsData>[] {
  const { comparisonLabel, comparisonColumns = OS_DEFAULT_COMPARISON_COLUMNS } = options

  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  return [
    {
      accessorKey: 'osName',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: 200,
      cell: ({ row }) => (
        <span className="truncate text-xs font-medium text-neutral-700">{row.original.osName}</span>
      ),
      enableSorting: false,
      meta: {
        title: __('Operating System', 'wp-statistics'),
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
