import type { ColumnDef, Row } from '@tanstack/react-table'
import { flexRender } from '@tanstack/react-table'
import { ChevronDown } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

import './data-table-types' // Import to extend ColumnMeta

interface DataTableCardProps<TData> {
  row: Row<TData>
  columns: ColumnDef<TData, unknown>[]
  isExpanded: boolean
  onToggleExpand: () => void
}

export function DataTableCard<TData>({ row, columns, isExpanded, onToggleExpand }: DataTableCardProps<TData>) {
  // Separate columns by priority
  const primaryColumns = columns.filter((col) => col.meta?.priority === 'primary')
  const secondaryColumns = columns.filter((col) => col.meta?.priority === 'secondary')

  // Separate primary columns by card position
  const headerColumns = primaryColumns.filter((col) => col.meta?.cardPosition === 'header')
  const bodyColumns = primaryColumns.filter((col) => col.meta?.cardPosition === 'body')

  // Fallback: if no columns have meta configured, show first 2 columns as header and next 3 as body
  const hasMeta = primaryColumns.length > 0 || secondaryColumns.length > 0
  const fallbackHeaderColumns = !hasMeta ? columns.slice(0, 2) : []
  const fallbackBodyColumns = !hasMeta ? columns.slice(2, 5) : []
  const fallbackSecondaryColumns = !hasMeta ? columns.slice(5) : []

  // Use fallback if no meta configured
  const displayHeaderColumns = headerColumns.length > 0 ? headerColumns : fallbackHeaderColumns
  const displayBodyColumns = bodyColumns.length > 0 ? bodyColumns : fallbackBodyColumns
  const displaySecondaryColumns = secondaryColumns.length > 0 ? secondaryColumns : (hasMeta ? columns.filter((col) => !col.meta?.priority) : fallbackSecondaryColumns)

  // Get cell content for a column - use getAllCells to include hidden columns for mobile card view
  const getCellContent = (column: ColumnDef<TData, unknown>) => {
    const columnId = column.id || (column as { accessorKey?: string }).accessorKey
    const cell = row.getAllCells().find((c) => c.column.id === columnId)
    if (!cell) return null
    return flexRender(cell.column.columnDef.cell, cell.getContext())
  }

  // Get column header text
  const getColumnHeader = (column: ColumnDef<TData, unknown>): string => {
    if (column.meta?.mobileLabel) return column.meta.mobileLabel
    if (typeof column.header === 'string') return column.header
    return (column as { accessorKey?: string }).accessorKey || ''
  }

  return (
    <div className="bg-white border border-neutral-200 rounded-lg overflow-hidden">
      {/* Header section - primary info prominently displayed */}
      <div className="flex items-start justify-between gap-3 p-3">
        {/* Primary header content */}
        <div className="flex-1 min-w-0 space-y-1">
          {displayHeaderColumns.map((col) => (
            <div key={col.id || (col as { accessorKey?: string }).accessorKey}>{getCellContent(col)}</div>
          ))}
        </div>

        {/* Expand button if secondary columns exist */}
        {displaySecondaryColumns.length > 0 && (
          <Button
            variant="ghost"
            size="icon"
            onClick={onToggleExpand}
            className="h-11 w-11 shrink-0 -mr-1 -mt-1"
            aria-expanded={isExpanded}
            aria-label={isExpanded ? 'Collapse details' : 'Expand details'}
          >
            <ChevronDown className={cn('h-4 w-4 transition-transform', isExpanded && 'rotate-180')} />
          </Button>
        )}
      </div>

      {/* Body section - key metrics in a grid */}
      {displayBodyColumns.length > 0 && (
        <div
          className={cn(
            'grid gap-3 px-3 pb-3 pt-0',
            displayBodyColumns.length === 1 && 'grid-cols-1',
            displayBodyColumns.length === 2 && 'grid-cols-2',
            displayBodyColumns.length >= 3 && 'grid-cols-3'
          )}
        >
          {displayBodyColumns.map((col) => (
            <div key={col.id || (col as { accessorKey?: string }).accessorKey} className="text-center">
              <div className="text-[10px] text-neutral-500 uppercase tracking-wide mb-0.5">{getColumnHeader(col)}</div>
              <div className="text-sm font-medium">{getCellContent(col)}</div>
            </div>
          ))}
        </div>
      )}

      {/* Expandable secondary section */}
      {isExpanded && displaySecondaryColumns.length > 0 && (
        <div className="border-t border-neutral-100 px-3 py-2 space-y-2 bg-neutral-50/50">
          {displaySecondaryColumns.map((col) => (
            <div
              key={col.id || (col as { accessorKey?: string }).accessorKey}
              className="flex justify-between items-center gap-2"
            >
              <span className="text-xs text-neutral-500 shrink-0">{getColumnHeader(col)}</span>
              <span className="text-xs text-right min-w-0">{getCellContent(col)}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
