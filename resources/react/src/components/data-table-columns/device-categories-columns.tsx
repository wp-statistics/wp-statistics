/**
 * Column definitions for the Device Categories data table.
 * Device analytics grouped by device type (desktop, mobile, tablet).
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { NumericCell } from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import type { DeviceCategoryRecord } from '@/services/devices/get-device-categories'

/**
 * Context identifier for user preferences
 */
export const DEVICE_CATEGORIES_CONTEXT = 'device-categories'

/**
 * Columns that support PP comparison display
 */
export const DEVICE_CATEGORIES_COMPARABLE_COLUMNS: string[] = ['visitors']

/**
 * Columns that show PP comparison by default
 */
export const DEVICE_CATEGORIES_DEFAULT_COMPARISON_COLUMNS: string[] = ['visitors']

/**
 * Device category data interface for the table
 */
export interface DeviceCategoryData {
  id: string
  deviceTypeName: string
  visitors: number
  previousVisitors?: number
  percentOfTotal: number
}

/**
 * Transform API response to DeviceCategoryData interface
 */
export function transformDeviceCategoryData(record: DeviceCategoryRecord, totalVisitors: number): DeviceCategoryData {
  const visitors = Number(record.visitors) || 0
  const deviceTypeName = record.device_type_name || 'Unknown'

  return {
    id: `device-type-${deviceTypeName}`,
    deviceTypeName,
    visitors,
    previousVisitors:
      record.previous?.visitors !== undefined ? Number(record.previous.visitors) : undefined,
    percentOfTotal: totalVisitors > 0 ? (visitors / totalVisitors) * 100 : 0,
  }
}

/**
 * Options for creating Device Categories columns
 */
export interface DeviceCategoriesColumnsOptions {
  comparisonLabel?: string
  comparisonColumns?: string[]
}

/**
 * Create column definitions for the Device Categories table
 */
export function createDeviceCategoriesColumns(options: DeviceCategoriesColumnsOptions): ColumnDef<DeviceCategoryData>[] {
  const { comparisonLabel, comparisonColumns = DEVICE_CATEGORIES_DEFAULT_COMPARISON_COLUMNS } = options

  const showComparison = (columnId: string) => comparisonColumns.includes(columnId)

  return [
    {
      accessorKey: 'deviceTypeName',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      size: 200,
      cell: ({ row }) => (
        <span className="truncate text-xs font-medium text-neutral-700">{row.original.deviceTypeName}</span>
      ),
      enableSorting: false,
      meta: {
        title: __('Device Type', 'wp-statistics'),
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
