import { Button } from '@components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@components/ui/table'
import { cn } from '@lib/utils'
import type { ColumnDef, ColumnFiltersState, SortingState, VisibilityState } from '@tanstack/react-table'
import {
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
} from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, Loader2 } from 'lucide-react'
import * as React from 'react'

import { Card, CardFooter, CardHeader, CardTitle } from '../ui/card'
import { DataTableColumnToggle } from './data-table-column-toggle'

interface FullReportLink {
  text: string
  action(): void
}

interface DataTableProps<TData, TValue> {
  columns: ColumnDef<TData, TValue>[]
  data: TData[]
  title?: string
  defaultSort?: string
  rowLimit?: number
  showColumnManagement?: boolean
  showPagination?: boolean
  fullReportLink?: FullReportLink
  hiddenColumns?: string[]
  emptyStateMessage?: string
  // Server-side sorting props
  sorting?: SortingState
  onSortingChange?: (sorting: SortingState) => void
  manualSorting?: boolean
  // Server-side pagination props
  manualPagination?: boolean
  pageCount?: number
  page?: number
  onPageChange?: (page: number) => void
  totalRows?: number
  // Loading state for server-side data fetching
  isFetching?: boolean
}

export function DataTable<TData, TValue>({
  columns,
  data,
  title,
  defaultSort,
  rowLimit = 50,
  showColumnManagement = true,
  showPagination = true,
  fullReportLink,
  hiddenColumns = [],
  emptyStateMessage = __('No data available', 'wp-statistics'),
  // Server-side sorting
  sorting: externalSorting,
  onSortingChange: externalOnSortingChange,
  manualSorting = false,
  // Server-side pagination
  manualPagination = false,
  pageCount: externalPageCount,
  page: externalPage,
  onPageChange,
  totalRows,
  // Loading state
  isFetching = false,
}: DataTableProps<TData, TValue>) {
  const [internalSorting, setInternalSorting] = React.useState<SortingState>(
    defaultSort ? [{ id: defaultSort, desc: true }] : []
  )
  const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([])
  const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>(
    hiddenColumns.reduce((acc, col) => ({ ...acc, [col]: false }), {})
  )
  const [rowSelection, setRowSelection] = React.useState({})

  // Use external sorting if provided, otherwise use internal
  const sorting = externalSorting ?? internalSorting
  const handleSortingChange = React.useCallback(
    (updaterOrValue: SortingState | ((old: SortingState) => SortingState)) => {
      const newValue = typeof updaterOrValue === 'function' ? updaterOrValue(sorting) : updaterOrValue
      if (externalOnSortingChange) {
        externalOnSortingChange(newValue)
      } else {
        setInternalSorting(newValue)
      }
    },
    [sorting, externalOnSortingChange]
  )

  // For manual pagination, track internal page index
  const [internalPageIndex, setInternalPageIndex] = React.useState(0)
  const pageIndex = manualPagination && externalPage !== undefined ? externalPage - 1 : internalPageIndex

  const table = useReactTable({
    data,
    columns,
    onSortingChange: handleSortingChange,
    onColumnFiltersChange: setColumnFilters,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: manualPagination ? undefined : getPaginationRowModel(),
    getSortedRowModel: manualSorting ? undefined : getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onColumnVisibilityChange: setColumnVisibility,
    onRowSelectionChange: setRowSelection,
    manualSorting,
    manualPagination,
    pageCount: manualPagination ? externalPageCount : undefined,
    initialState: {
      pagination: {
        pageSize: rowLimit,
      },
    },
    state: {
      sorting,
      columnFilters,
      columnVisibility,
      rowSelection,
      pagination: {
        pageIndex,
        pageSize: rowLimit,
      },
    },
    onPaginationChange: manualPagination
      ? (updater) => {
          const newState = typeof updater === 'function' ? updater({ pageIndex, pageSize: rowLimit }) : updater
          if (onPageChange && newState.pageIndex !== pageIndex) {
            onPageChange(newState.pageIndex + 1)
          } else {
            setInternalPageIndex(newState.pageIndex)
          }
        }
      : undefined,
  })

  return (
    <Card className="overflow-hidden min-w-0">
      {title && (
        <CardHeader className="pb-0">
          <div className="flex items-center gap-2">
            <CardTitle className="">{title}</CardTitle>
            {isFetching && <Loader2 className="h-4 w-4 animate-spin text-muted-foreground" />}
          </div>
        </CardHeader>
      )}
      <div className={cn('overflow-x-auto min-w-0 relative', isFetching && 'opacity-50 pointer-events-none transition-opacity')}>
        {isFetching && (
          <div className="absolute inset-0 flex items-center justify-center z-10 bg-white/30">
            <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
          </div>
        )}
        <Table className="min-w-max">
          <TableHeader>
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id} className="border-0 bg-white hover:bg-white">
                {headerGroup.headers.map((header, index) => {
                  return (
                    <TableHead key={header.id} className={cn('h-12', index === 0 ? 'pl-6' : '')}>
                      {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                    </TableHead>
                  )
                })}
                {showColumnManagement && (
                  <TableHead className="h-12 w-12 pr-6">
                    <DataTableColumnToggle table={table} />
                  </TableHead>
                )}
              </TableRow>
            ))}
          </TableHeader>
          <TableBody>
            {table.getRowModel().rows?.length ? (
              table.getRowModel().rows.map((row, rowIndex) => (
                <TableRow
                  key={row.id}
                  data-state={row.getIsSelected() && 'selected'}
                  className={cn(
                    'border-0',
                    rowIndex % 2 === 0 ? 'bg-white hover:bg-slate-100' : 'bg-slate-50 hover:bg-slate-100'
                  )}
                >
                  {row.getVisibleCells().map((cell, cellIndex) => (
                    <TableCell
                      key={cell.id}
                      className={cn(
                        cellIndex === 0 ? 'pl-6' : '',
                        cellIndex === row.getVisibleCells().length - 1 && !showColumnManagement ? 'pr-6' : ''
                      )}
                    >
                      {flexRender(cell.column.columnDef.cell, cell.getContext())}
                    </TableCell>
                  ))}
                  {showColumnManagement && <TableCell className="w-12 pr-6" />}
                </TableRow>
              ))
            ) : (
              <TableRow className="border-0">
                <TableCell
                  colSpan={columns.length + (showColumnManagement ? 1 : 0)}
                  className="h-24 text-center text-sm text-card-foreground pl-6"
                >
                  {isFetching ? null : emptyStateMessage}
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>
      <CardFooter className="flex flex-col items-stretch">
        {showPagination && (
          <div className="flex items-center justify-between flex-wrap gap-4">
            <div className="text-sm text-card-foreground">
              {(() => {
                const rowCount = manualPagination && totalRows !== undefined ? totalRows : table.getFilteredRowModel().rows.length
                if (rowCount > 0) {
                  const start = table.getState().pagination.pageIndex * table.getState().pagination.pageSize + 1
                  const end = Math.min(
                    (table.getState().pagination.pageIndex + 1) * table.getState().pagination.pageSize,
                    rowCount
                  )
                  return `${start}-${end} of ${rowCount} Items`
                }
                return '0 Items'
              })()}
            </div>
            <div className="flex items-center gap-2">
              <Button
                variant="outline"
                size="icon"
                onClick={() => table.setPageIndex(0)}
                disabled={!table.getCanPreviousPage()}
                className="h-10 w-10 rounded-md"
              >
                <ChevronsLeft className="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                onClick={() => table.previousPage()}
                disabled={!table.getCanPreviousPage()}
                className="h-10 px-4 rounded-md"
              >
                <ChevronLeft className="h-4 w-4 mr-1" />
                Previous
              </Button>
              {(() => {
                const currentPage = table.getState().pagination.pageIndex + 1
                const totalPages = table.getPageCount()
                const pages: (number | string)[] = []

                if (totalPages <= 7) {
                  for (let i = 1; i <= totalPages; i++) {
                    pages.push(i)
                  }
                } else {
                  pages.push(1)
                  if (currentPage > 3) {
                    pages.push('...')
                  }
                  const start = Math.max(2, currentPage - 1)
                  const end = Math.min(totalPages - 1, currentPage + 1)
                  for (let i = start; i <= end; i++) {
                    pages.push(i)
                  }
                  if (currentPage < totalPages - 2) {
                    pages.push('...')
                  }
                  pages.push(totalPages)
                }

                return pages.map((page, index) =>
                  typeof page === 'number' ? (
                    <Button
                      key={index}
                      variant={currentPage === page ? 'default' : 'outline'}
                      size="icon"
                      onClick={() => table.setPageIndex(page - 1)}
                      className="h-10 w-10 rounded-md"
                    >
                      {page}
                    </Button>
                  ) : (
                    <span key={index} className="px-2 text-card-foreground">
                      {page}
                    </span>
                  )
                )
              })()}
              <Button
                variant="outline"
                onClick={() => table.nextPage()}
                disabled={!table.getCanNextPage()}
                className="h-10 px-4 rounded-md"
              >
                Next
                <ChevronRight className="h-4 w-4 ml-1" />
              </Button>
              <Button
                variant="outline"
                size="icon"
                onClick={() => table.setPageIndex(table.getPageCount() - 1)}
                disabled={!table.getCanNextPage()}
                className="h-10 w-10 rounded-md"
              >
                <ChevronsRight className="h-4 w-4" />
              </Button>
              <div className="flex items-center gap-2 ml-2">
                <span className="text-sm text-card-foreground">Page:</span>
                <input
                  type="number"
                  min="1"
                  max={table.getPageCount()}
                  value={table.getState().pagination.pageIndex + 1}
                  onChange={(e) => {
                    const page = e.target.value ? Number(e.target.value) - 1 : 0
                    table.setPageIndex(page)
                  }}
                  className="w-16 h-10 px-2 text-sm border border-input rounded-md text-center"
                />
                <Button variant="outline" onClick={() => {}} className="h-10 px-4 rounded-md">
                  Go
                </Button>
              </div>
            </div>
          </div>
        )}
        {fullReportLink && (
          <Button
            variant="link"
            onClick={fullReportLink.action}
            className="text-sm text-card-foreground font-normal flex items-center gap-1 justify-self-end hover:no-underline ms-auto mt-4 has-[>svg]:px-0"
          >
            {fullReportLink.text || __('View Full Report', 'wp-statistics')}
            <ChevronRight />
          </Button>
        )}
      </CardFooter>
    </Card>
  )
}
