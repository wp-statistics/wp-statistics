/**
 * Column definitions for the Timezones data table.
 * Geographic analytics grouped by IANA timezone.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { NumericCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import type { TimezoneRecord } from '@/services/geographic/get-timezones'

/**
 * Context identifier for user preferences
 */
export const TIMEZONES_CONTEXT = 'timezones'

/**
 * Columns hidden by default
 */
export const TIMEZONES_DEFAULT_HIDDEN_COLUMNS: string[] = []

/**
 * Columns that show PP comparison by default
 */
export const TIMEZONES_DEFAULT_COMPARISON_COLUMNS: string[] = ['visitors']

/**
 * Columns that support PP comparison display
 */
export const TIMEZONES_COMPARABLE_COLUMNS: string[] = ['visitors', 'views']

/**
 * Column configuration for API column optimization
 */
export const TIMEZONES_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['timezone_id', 'timezone_name', 'timezone_offset'],
  columnDependencies: {
    timezone: ['timezone_id', 'timezone_name', 'timezone_offset'],
    visitors: ['visitors'],
    views: ['views'],
  },
  context: TIMEZONES_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const TIMEZONES_DEFAULT_API_COLUMNS = getDefaultApiColumns(TIMEZONES_COLUMN_CONFIG)

/**
 * Timezone data interface for the table
 */
export interface TimezoneData {
  id: string
  timezoneName: string
  timezoneOffset: string
  visitors: number
  views: number
  previousVisitors?: number
  previousViews?: number
}

/**
 * Format UTC offset string for display (e.g., "+05:30" → "UTC+05:30")
 */
function formatUtcOffset(offset: string): string {
  if (!offset) return ''
  if (offset.startsWith('+') || offset.startsWith('-')) return `UTC${offset}`
  return `UTC+${offset}`
}

/**
 * Transform API response to TimezoneData interface
 */
export function transformTimezoneData(record: TimezoneRecord): TimezoneData {
  const previous = record.previous
  const offset = String(record.timezone_offset || '')

  return {
    id: `timezone-${record.timezone_id || 0}`,
    timezoneName: record.timezone_name
      ? `${record.timezone_name} (${formatUtcOffset(offset)})`
      : __('Unknown', 'wp-statistics'),
    timezoneOffset: offset,
    visitors: Number(record.visitors) || 0,
    views: Number(record.views) || 0,
    ...(previous && {
      previousVisitors: previous.visitors !== undefined ? Number(previous.visitors) : undefined,
      previousViews: previous.views !== undefined ? Number(previous.views) : undefined,
    }),
  }
}

/**
 * Options for creating Timezones columns
 */
export interface TimezonesColumnsOptions {
  comparisonLabel?: string
  comparisonColumns?: string[]
}

/**
 * Create column definitions for the Timezones table
 */
export function createTimezonesColumns(options: TimezonesColumnsOptions): ColumnDef<TimezoneData>[] {
  const { comparisonLabel, comparisonColumns = TIMEZONES_DEFAULT_COMPARISON_COLUMNS } = options

  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  return [
    {
      accessorKey: 'timezone',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: 240,
      cell: ({ row }) => (
        <span className="text-xs font-medium text-neutral-700 truncate">
          {row.original.timezoneName}
        </span>
      ),
      enableSorting: false,
      meta: {
        title: __('Timezone', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'header',
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
        priority: 'primary',
        cardPosition: 'body',
        isComparable: true,
        showComparison: showComparison('visitors'),
      },
    },
    {
      accessorKey: 'views',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => (
        <NumericCell
          value={row.original.views}
          previousValue={showComparison('views') ? row.original.previousViews : undefined}
          comparisonLabel={comparisonLabel}
        />
      ),
      meta: {
        title: __('Views', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
        isComparable: true,
        showComparison: showComparison('views'),
      },
    },
  ]
}
