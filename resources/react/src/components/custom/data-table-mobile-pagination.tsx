import type { Table } from '@tanstack/react-table'
import { ChevronLeft, ChevronRight } from 'lucide-react'

import { Button } from '@/components/ui/button'

interface DataTableMobilePaginationProps<TData> {
  table: Table<TData>
  totalRows?: number
}

export function DataTableMobilePagination<TData>({ table, totalRows }: DataTableMobilePaginationProps<TData>) {
  const currentPage = table.getState().pagination.pageIndex + 1
  const totalPages = table.getPageCount()

  if (totalPages <= 1) return null

  return (
    <div className="flex items-center justify-between px-3 py-3 border-t border-neutral-200 bg-white">
      {/* Page info */}
      <div className="text-xs text-neutral-500">
        <span className="font-medium text-neutral-700">{currentPage}</span>
        <span> / {totalPages}</span>
        {totalRows !== undefined && (
          <span className="text-neutral-400"> ({totalRows.toLocaleString()})</span>
        )}
      </div>

      {/* Navigation buttons - 44px touch targets */}
      <div className="flex gap-2">
        <Button
          variant="outline"
          size="icon"
          onClick={() => table.previousPage()}
          disabled={!table.getCanPreviousPage()}
          className="h-11 w-11"
          aria-label="Previous page"
        >
          <ChevronLeft className="h-5 w-5" />
        </Button>
        <Button
          variant="outline"
          size="icon"
          onClick={() => table.nextPage()}
          disabled={!table.getCanNextPage()}
          className="h-11 w-11"
          aria-label="Next page"
        >
          <ChevronRight className="h-5 w-5" />
        </Button>
      </div>
    </div>
  )
}
