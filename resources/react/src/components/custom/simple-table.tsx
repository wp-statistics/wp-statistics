/**
 * SimpleTable - A lightweight, reusable table component for fixed-row data displays
 *
 * Use SimpleTable when you have a small, fixed set of rows that don't need:
 * - Sorting
 * - Pagination
 * - Column resizing
 *
 * For those features, use DataTable instead.
 *
 * @example
 * ```tsx
 * <SimpleTable
 *   title="Traffic Summary"
 *   columns={[
 *     { key: 'period', header: 'Time Period', cell: (row) => row.label },
 *     { key: 'visitors', header: 'Visitors', align: 'right', cell: (row) => <NumericCell value={row.visitors} /> },
 *   ]}
 *   data={rows}
 *   isLoading={isLoading}
 * />
 * ```
 */

import { __ } from '@wordpress/i18n'
import type { ReactNode } from 'react'

import { Panel, PanelContent, PanelHeader, PanelTitle } from '@/components/ui/panel'
import { Skeleton } from '@/components/ui/skeleton'
import { cn } from '@/lib/utils'

export interface SimpleTableColumn<T> {
  /** Unique key for the column */
  key: string
  /** Column header text */
  header: string
  /** Render function for the cell content */
  cell: (row: T) => ReactNode
  /** Text alignment (default: 'left') */
  align?: 'left' | 'center' | 'right'
  /** Optional additional className for the column */
  className?: string
}

export interface SimpleTableProps<T> {
  /** Optional title (renders inside Panel header) */
  title?: string
  /** Column definitions */
  columns: SimpleTableColumn<T>[]
  /** Data rows */
  data: T[]
  /** Loading state - shows skeleton */
  isLoading?: boolean
  /** Message shown when data is empty */
  emptyMessage?: string
  /** Optional additional className for the table */
  className?: string
}

/**
 * Loading skeleton for SimpleTable
 */
function SimpleTableSkeleton({ columns, rows = 5 }: { columns: number; rows?: number }) {
  return (
    <div className="w-full">
      {/* Header skeleton */}
      <div className="flex gap-4 pb-2 border-b border-neutral-100">
        {Array.from({ length: columns }).map((_, i) => (
          <Skeleton key={`header-${i}`} className="h-4 flex-1" />
        ))}
      </div>
      {/* Row skeletons */}
      {Array.from({ length: rows }).map((_, rowIndex) => (
        <div key={`row-${rowIndex}`} className="flex gap-4 py-2 border-b border-neutral-100 last:border-b-0">
          {Array.from({ length: columns }).map((_, colIndex) => (
            <Skeleton key={`cell-${rowIndex}-${colIndex}`} className={cn('h-4 flex-1', colIndex === 0 && 'max-w-[150px]')} />
          ))}
        </div>
      ))}
    </div>
  )
}

/**
 * Empty state component
 */
function SimpleTableEmpty({ message }: { message: string }) {
  return (
    <div className="py-8 text-center text-xs text-muted-foreground">
      {message}
    </div>
  )
}

export function SimpleTable<T>({
  title,
  columns,
  data,
  isLoading = false,
  emptyMessage = __('No data available', 'wp-statistics'),
  className,
}: SimpleTableProps<T>) {
  const getAlignmentClass = (align?: 'left' | 'center' | 'right') => {
    switch (align) {
      case 'center':
        return 'text-center'
      case 'right':
        return 'text-right'
      default:
        return 'text-left'
    }
  }

  const tableContent = (
    <>
      {isLoading ? (
        <SimpleTableSkeleton columns={columns.length} rows={5} />
      ) : data.length === 0 ? (
        <SimpleTableEmpty message={emptyMessage} />
      ) : (
        <table className={cn('w-full', className)}>
          <thead>
            <tr className="border-b border-neutral-100">
              {columns.map((column) => (
                <th
                  key={column.key}
                  className={cn(
                    'pb-2 text-xs font-semibold text-neutral-500',
                    getAlignmentClass(column.align),
                    column.className
                  )}
                >
                  {column.header}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {data.map((row, rowIndex) => (
              <tr
                key={rowIndex}
                className="border-b border-neutral-100 last:border-b-0"
              >
                {columns.map((column) => (
                  <td
                    key={column.key}
                    className={cn(
                      'py-2 text-xs text-neutral-700',
                      getAlignmentClass(column.align),
                      column.className
                    )}
                  >
                    {column.cell(row)}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </>
  )

  // If title is provided, wrap in Panel with header
  if (title) {
    return (
      <Panel className="h-full flex flex-col">
        <PanelHeader>
          <PanelTitle>{title}</PanelTitle>
        </PanelHeader>
        <PanelContent>{tableContent}</PanelContent>
      </Panel>
    )
  }

  // Otherwise, just render the table
  return tableContent
}
