import * as React from 'react'
import type { ColumnDef, ColumnFiltersState, SortingState, VisibilityState } from '@tanstack/react-table'
import {
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
} from '@tanstack/react-table'
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-react'

import { Button } from '@components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@components/ui/table'
import { DataTableColumnToggle } from './data-table-column-toggle'
import { __ } from '@wordpress/i18n'
import { Card, CardFooter, CardHeader, CardTitle } from '../ui/card'
import { cn } from '@lib/utils'

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
}: DataTableProps<TData, TValue>) {
  const [sorting, setSorting] = React.useState<SortingState>(defaultSort ? [{ id: defaultSort, desc: true }] : [])
  const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([])
  const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({})
  const [rowSelection, setRowSelection] = React.useState({})

  const table = useReactTable({
    data,
    columns,
    onSortingChange: setSorting,
    onColumnFiltersChange: setColumnFilters,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onColumnVisibilityChange: setColumnVisibility,
    onRowSelectionChange: setRowSelection,
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
    },
  })

  return (
    <Card className="overflow-hidden">
      {title && (
        <CardHeader className="pb-0">
          <CardTitle className="">{title}</CardTitle>
        </CardHeader>
      )}
      <Table>
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
                No data available
              </TableCell>
            </TableRow>
          )}
        </TableBody>
      </Table>
      <CardFooter className="flex flex-col items-stretch">
        {showPagination && (
          <div className="flex items-center justify-between flex-wrap gap-4">
            <div className="text-sm text-card-foreground">
              {table.getFilteredRowModel().rows.length > 0 ? (
                <>
                  {table.getState().pagination.pageIndex * table.getState().pagination.pageSize + 1}-
                  {Math.min(
                    (table.getState().pagination.pageIndex + 1) * table.getState().pagination.pageSize,
                    table.getFilteredRowModel().rows.length
                  )}{' '}
                  of {table.getFilteredRowModel().rows.length} Items
                </>
              ) : (
                '0 Items'
              )}
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
