import type { ColumnDef, Table } from '@tanstack/react-table'
import { Loader2 } from 'lucide-react'
import * as React from 'react'

import { cn } from '@/lib/utils'

import { DataTableCard } from './data-table-card'

interface DataTableCardListProps<TData> {
  table: Table<TData>
  columns: ColumnDef<TData, unknown>[]
  emptyStateMessage?: string
  isFetching?: boolean
}

export function DataTableCardList<TData>({
  table,
  columns,
  emptyStateMessage,
  isFetching,
}: DataTableCardListProps<TData>) {
  const [expandedRows, setExpandedRows] = React.useState<Set<string>>(new Set())

  const toggleExpand = React.useCallback((rowId: string) => {
    setExpandedRows((prev) => {
      const next = new Set(prev)
      if (next.has(rowId)) {
        next.delete(rowId)
      } else {
        next.add(rowId)
      }
      return next
    })
  }, [])

  const rows = table.getRowModel().rows

  if (!rows.length && !isFetching) {
    return (
      <div className="p-8 text-center text-sm text-neutral-500">
        {emptyStateMessage}
      </div>
    )
  }

  return (
    <div
      className={cn(
        'space-y-2 p-3 relative',
        isFetching && 'opacity-50 pointer-events-none'
      )}
    >
      {isFetching && (
        <div className="absolute inset-0 flex items-center justify-center z-10 bg-white/30">
          <Loader2 className="h-5 w-5 animate-spin text-neutral-400" />
        </div>
      )}
      {rows.map((row) => (
        <DataTableCard
          key={row.id}
          row={row}
          columns={columns}
          isExpanded={expandedRows.has(row.id)}
          onToggleExpand={() => toggleExpand(row.id)}
        />
      ))}
    </div>
  )
}
