import * as React from 'react'
import type {
  ColumnDef,
  ColumnFiltersState,
  SortingState,
  VisibilityState,
} from '@tanstack/react-table'
import {
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
} from '@tanstack/react-table'
import { MoreVertical } from 'lucide-react'

import { Button } from '@components/ui/button'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from '@components/ui/dropdown-menu'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@components/ui/table'

interface DataTableProps<TData, TValue> {
  columns: ColumnDef<TData, TValue>[]
  data: TData[]
  title?: string
  defaultSort?: string
  rowLimit?: number
  showColumnManagement?: boolean
  showPagination?: boolean
}

export function DataTable<TData, TValue>({
  columns,
  data,
  title,
  defaultSort,
  rowLimit = 50,
  showColumnManagement = true,
  showPagination = true,
}: DataTableProps<TData, TValue>) {
  const [sorting, setSorting] = React.useState<SortingState>(
    defaultSort ? [{ id: defaultSort, desc: true }] : []
  )
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
    <div className="w-full">
      {title && (
        <div className="flex items-center justify-between py-4">
          <h2 className="text-lg font-semibold">{title}</h2>
        </div>
      )}
      <div className="rounded-lg border bg-white">
        <Table>
          <TableHeader>
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id} className="border-b bg-white hover:bg-white">
                {headerGroup.headers.map((header) => {
                  return (
                    <TableHead key={header.id} className="h-12 bg-white">
                      {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                    </TableHead>
                  )
                })}
                {showColumnManagement && (
                  <TableHead className="h-12 w-12 bg-white">
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                          <MoreVertical className="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" className="w-[200px]">
                        {table
                          .getAllColumns()
                          .filter((column) => column.getCanHide())
                          .map((column) => {
                            return (
                              <DropdownMenuCheckboxItem
                                key={column.id}
                                className="capitalize"
                                checked={column.getIsVisible()}
                                onCheckedChange={(value) => column.toggleVisibility(!!value)}
                              >
                                {column.id}
                              </DropdownMenuCheckboxItem>
                            )
                          })}
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableHead>
                )}
              </TableRow>
            ))}
          </TableHeader>
          <TableBody>
            {table.getRowModel().rows?.length ? (
              table.getRowModel().rows.map((row, index) => (
                <TableRow
                  key={row.id}
                  data-state={row.getIsSelected() && 'selected'}
                  className={index % 2 === 0 ? 'bg-neutral-50 hover:bg-neutral-100' : 'bg-white hover:bg-neutral-100'}
                >
                  {row.getVisibleCells().map((cell) => (
                    <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                  ))}
                  {showColumnManagement && <TableCell className="w-12" />}
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={columns.length + (showColumnManagement ? 1 : 0)} className="h-24 text-center">
                  No data available
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>
      {showPagination && (
        <div className="flex items-center justify-center gap-2 py-4 bg-muted/10 border-t">
          <Button
            variant="ghost"
            size="sm"
            onClick={() => table.previousPage()}
            disabled={!table.getCanPreviousPage()}
            className="text-sm"
          >
            Prev
          </Button>
          <div className="flex items-center gap-1">
            <Button
              variant={table.getState().pagination.pageIndex === 0 ? 'default' : 'ghost'}
              size="sm"
              onClick={() => table.setPageIndex(0)}
              className="h-8 w-8 p-0"
            >
              1
            </Button>
            {table.getPageCount() > 1 && (
              <Button
                variant={table.getState().pagination.pageIndex === 1 ? 'default' : 'ghost'}
                size="sm"
                onClick={() => table.setPageIndex(1)}
                className="h-8 w-8 p-0"
              >
                2
              </Button>
            )}
            {table.getPageCount() > 2 && (
              <Button
                variant={table.getState().pagination.pageIndex === 2 ? 'default' : 'ghost'}
                size="sm"
                onClick={() => table.setPageIndex(2)}
                className="h-8 w-8 p-0"
              >
                3
              </Button>
            )}
            {table.getPageCount() > 4 && <span className="px-2 text-muted-foreground">...</span>}
            {table.getPageCount() > 3 && (
              <Button
                variant={table.getState().pagination.pageIndex === table.getPageCount() - 1 ? 'default' : 'ghost'}
                size="sm"
                onClick={() => table.setPageIndex(table.getPageCount() - 1)}
                className="h-8 w-8 p-0"
              >
                {table.getPageCount()}
              </Button>
            )}
          </div>
          <Button
            variant="ghost"
            size="sm"
            onClick={() => table.nextPage()}
            disabled={!table.getCanNextPage()}
            className="text-sm"
          >
            Next
          </Button>
        </div>
      )}
    </div>
  )
}
