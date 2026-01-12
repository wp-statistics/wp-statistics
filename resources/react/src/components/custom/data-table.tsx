import './data-table-types' // Import to extend ColumnMeta

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
import { ChevronLeft, ChevronRight, Loader2 } from 'lucide-react'
import * as React from 'react'

import { useIsMobile } from '@/hooks/use-mobile'

import { EmptyState } from '../ui/empty-state'
import { Panel, PanelAction, PanelFooter, PanelHeader, PanelTitle } from '../ui/panel'
import { DataTableCardList } from './data-table-card-list'
import { DataTableColumnToggle } from './data-table-column-toggle'
import { DataTableMobileHeader } from './data-table-mobile-header'
import { DataTableMobilePagination } from './data-table-mobile-pagination'

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
  // Column preferences props
  initialColumnVisibility?: VisibilityState
  columnOrder?: string[]
  onColumnVisibilityChange?: (visibility: VisibilityState) => void
  onColumnOrderChange?: (order: string[]) => void
  onColumnPreferencesReset?: () => void
  // Mobile card view
  mobileCardEnabled?: boolean
  // Sticky header for scrollable tables
  stickyHeader?: boolean
  // Borderless panel variant for single-widget report pages
  borderless?: boolean
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
  // Column preferences
  initialColumnVisibility,
  columnOrder,
  onColumnVisibilityChange,
  onColumnOrderChange,
  onColumnPreferencesReset,
  // Mobile card view
  mobileCardEnabled = true,
  // Sticky header
  stickyHeader = false,
  // Borderless panel
  borderless = false,
}: DataTableProps<TData, TValue>) {
  const isMobile = useIsMobile()

  // Extract sortable columns for mobile sort dropdown
  const sortableColumns = React.useMemo(() => {
    return columns
      .filter((col) => col.enableSorting !== false)
      .map((col) => ({
        id: (col as { accessorKey?: string }).accessorKey || col.id || '',
        label:
          typeof col.header === 'string'
            ? col.header
            : col.meta?.mobileLabel || (col as { accessorKey?: string }).accessorKey || col.id || '',
      }))
      .filter((col) => col.id)
  }, [columns])
  const [internalSorting, setInternalSorting] = React.useState<SortingState>(
    defaultSort ? [{ id: defaultSort, desc: true }] : []
  )
  const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([])
  const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>(() =>
    hiddenColumns.reduce((acc, col) => ({ ...acc, [col]: false }), {})
  )
  const [internalColumnOrder, setInternalColumnOrder] = React.useState<string[]>([])
  const internalColumnOrderRef = React.useRef<string[]>([])

  // Sync initial visibility when it changes (e.g., from API preferences or reset)
  const hasAppliedInitialVisibility = React.useRef(false)
  const prevInitialVisibilityRef = React.useRef<VisibilityState | undefined>(initialColumnVisibility)
  React.useEffect(() => {
    const currentKeys = initialColumnVisibility ? Object.keys(initialColumnVisibility).length : 0
    const prevKeys = prevInitialVisibilityRef.current ? Object.keys(prevInitialVisibilityRef.current).length : 0

    // Apply initial visibility from preferences (only once)
    if (currentKeys > 0 && !hasAppliedInitialVisibility.current) {
      setColumnVisibility(initialColumnVisibility!)
      hasAppliedInitialVisibility.current = true
    }
    // Handle reset: if visibility changes after being initially applied (e.g., reset to defaults)
    else if (
      hasAppliedInitialVisibility.current &&
      currentKeys > 0 &&
      prevKeys > 0 &&
      initialColumnVisibility !== prevInitialVisibilityRef.current
    ) {
      // Check if this is a reset (visibility changed significantly)
      const visibilityChanged =
        JSON.stringify(initialColumnVisibility) !== JSON.stringify(prevInitialVisibilityRef.current)
      if (visibilityChanged) {
        setColumnVisibility(initialColumnVisibility!)
      }
    }
    prevInitialVisibilityRef.current = initialColumnVisibility
  }, [initialColumnVisibility])

  // Keep ref in sync with state for use in callbacks
  React.useEffect(() => {
    internalColumnOrderRef.current = internalColumnOrder
  }, [internalColumnOrder])

  // Sync column order when it changes (e.g., from API preferences or reset)
  const hasAppliedInitialColumnOrder = React.useRef(false)
  const prevColumnOrderRef = React.useRef<string[] | undefined>(columnOrder)
  React.useEffect(() => {
    // Apply initial column order from preferences (only once)
    if (columnOrder && columnOrder.length > 0 && !hasAppliedInitialColumnOrder.current) {
      setInternalColumnOrder(columnOrder)
      internalColumnOrderRef.current = columnOrder
      hasAppliedInitialColumnOrder.current = true
    }
    // Handle reset: if columnOrder prop becomes empty/undefined after being set
    else if (
      hasAppliedInitialColumnOrder.current &&
      (!columnOrder || columnOrder.length === 0) &&
      prevColumnOrderRef.current &&
      prevColumnOrderRef.current.length > 0
    ) {
      setInternalColumnOrder([])
      internalColumnOrderRef.current = []
      hasAppliedInitialColumnOrder.current = false
    }
    prevColumnOrderRef.current = columnOrder
  }, [columnOrder])

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

  // Handle column order changes from the table
  const handleColumnOrderChange = React.useCallback(
    (updaterOrValue: string[] | ((old: string[]) => string[])) => {
      const newValue =
        typeof updaterOrValue === 'function' ? updaterOrValue(internalColumnOrderRef.current) : updaterOrValue
      setInternalColumnOrder(newValue)
      internalColumnOrderRef.current = newValue
      if (onColumnOrderChange) {
        onColumnOrderChange(newValue)
      }
    },
    [onColumnOrderChange]
  )

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
    onColumnOrderChange: handleColumnOrderChange,
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
      columnOrder: internalColumnOrder.length > 0 ? internalColumnOrder : undefined,
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

  // Extract values for memoization dependencies
  const currentPageIndex = table.getState().pagination.pageIndex
  const totalPages = table.getPageCount()

  // Memoize pagination page numbers
  const paginationPages = React.useMemo(() => {
    const currentPage = currentPageIndex + 1
    const pages: (number | string)[] = []

    if (totalPages <= 5) {
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
    return pages
  }, [currentPageIndex, totalPages])

  // Memoize pagination handlers
  const handlePreviousPage = React.useCallback(() => {
    table.previousPage()
  }, [table])

  const handleNextPage = React.useCallback(() => {
    table.nextPage()
  }, [table])

  const handleSetPageIndex = React.useCallback(
    (index: number) => {
      table.setPageIndex(index)
    },
    [table]
  )

  // Mobile card view
  if (isMobile && mobileCardEnabled) {
    return (
      <Panel variant={borderless ? 'borderless' : 'default'} className="overflow-hidden min-w-0">
        <DataTableMobileHeader
          title={title}
          sorting={sorting}
          onSortingChange={externalOnSortingChange || setInternalSorting}
          sortableColumns={sortableColumns}
          isFetching={isFetching}
        />
        <DataTableCardList
          table={table}
          columns={columns as ColumnDef<TData, unknown>[]}
          emptyStateMessage={emptyStateMessage}
          isFetching={isFetching}
        />
        {showPagination && (totalRows ?? 0) > 0 && <DataTableMobilePagination table={table} totalRows={totalRows!} />}
      </Panel>
    )
  }

  // Desktop/tablet table view
  return (
    <Panel variant={borderless ? 'borderless' : 'default'} className="min-w-0 overflow-hidden">
      {title && (
        <PanelHeader className="pb-0">
          <PanelTitle>{title}</PanelTitle>
          {isFetching && <Loader2 className="h-4 w-4 animate-spin text-neutral-400" />}
        </PanelHeader>
      )}
      <div
        className={cn(
          'min-w-0 relative',
          stickyHeader ? 'overflow-auto max-h-[70vh]' : 'overflow-x-auto',
          isFetching && 'opacity-50 pointer-events-none transition-opacity'
        )}
      >
        {isFetching && (
          <div className="absolute inset-0 flex items-center justify-center z-10 bg-white/30">
            <Loader2 className="h-5 w-5 animate-spin text-neutral-400" />
          </div>
        )}
        <Table className="min-w-max">
          <TableHeader className={cn(stickyHeader && 'sticky top-0 z-10')}>
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id} className="border-0 bg-white hover:bg-white">
                {headerGroup.headers.map((header, index) => {
                  const size = header.column.columnDef.size
                  const align = (header.column.columnDef.meta as { align?: 'left' | 'right' | 'center' } | undefined)?.align
                  return (
                    <TableHead
                      key={header.id}
                      className={cn(
                        'h-8 bg-white',
                        index === 0 ? 'pl-4' : '',
                        index === headerGroup.headers.length - 1 ? 'pr-4' : '',
                        align === 'right' && 'text-right',
                        align === 'center' && 'text-center'
                      )}
                      style={size ? { width: size, minWidth: size, maxWidth: size } : undefined}
                    >
                      {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                    </TableHead>
                  )
                })}
              </TableRow>
            ))}
          </TableHeader>
          <TableBody>
            {table.getRowModel().rows?.length ? (
              table.getRowModel().rows.map((row, rowIndex) => (
                <TableRow
                  key={row.id}
                  className={cn(
                    'border-0 transition-colors',
                    rowIndex % 2 === 0 ? 'bg-white hover:bg-neutral-50' : 'bg-neutral-50/50 hover:bg-neutral-100/70'
                  )}
                >
                  {row.getVisibleCells().map((cell, cellIndex) => (
                    <TableCell
                      key={cell.id}
                      className={cn(
                        cellIndex === 0 ? 'pl-4' : '',
                        cellIndex === row.getVisibleCells().length - 1 ? 'pr-4' : ''
                      )}
                    >
                      {flexRender(cell.column.columnDef.cell, cell.getContext())}
                    </TableCell>
                  ))}
                </TableRow>
              ))
            ) : (
              <TableRow className="border-0 hover:bg-transparent">
                <TableCell colSpan={columns.length} className="p-0">
                  {isFetching ? null : <EmptyState title={emptyStateMessage} className="py-8" />}
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>
      {(showColumnManagement || showPagination || fullReportLink) && (
        <PanelFooter className="grid grid-cols-3 items-center">
          {/* Left: Column toggle */}
          <div className="justify-self-start">
            {showColumnManagement && (
              <DataTableColumnToggle
                table={table}
                initialColumnOrder={columnOrder}
                defaultHiddenColumns={hiddenColumns}
                onColumnVisibilityChange={onColumnVisibilityChange}
                onColumnOrderChange={onColumnOrderChange}
                onReset={onColumnPreferencesReset}
              />
            )}
          </div>

          {/* Center: Pagination */}
          <div className="justify-self-center">
            {showPagination && (totalRows ?? 0) > 0 && (
              <div className="flex items-center gap-0.5">
                <Button
                  variant="ghost"
                  onClick={handlePreviousPage}
                  disabled={!table.getCanPreviousPage()}
                  className="h-7 px-2 text-xs"
                >
                  <ChevronLeft className="h-3.5 w-3.5" />
                  Prev
                </Button>
                {paginationPages.map((page, index) => {
                  const currentPage = currentPageIndex + 1
                  return typeof page === 'number' ? (
                    <Button
                      key={index}
                      variant={currentPage === page ? 'default' : 'ghost'}
                      size="icon"
                      onClick={() => handleSetPageIndex(page - 1)}
                      className="h-7 w-7 text-xs"
                    >
                      {page}
                    </Button>
                  ) : (
                    <span key={index} className="px-1 text-neutral-400 text-xs">
                      {page}
                    </span>
                  )
                })}
                <Button
                  variant="ghost"
                  onClick={handleNextPage}
                  disabled={!table.getCanNextPage()}
                  className="h-7 px-2 text-xs"
                >
                  Next
                  <ChevronRight className="h-3.5 w-3.5" />
                </Button>
              </div>
            )}
          </div>

          {/* Right: Full report link */}
          <div className="justify-self-end">
            {fullReportLink && (
              <PanelAction onClick={fullReportLink.action}>
                {fullReportLink.text || __('View Full Report', 'wp-statistics')}
              </PanelAction>
            )}
          </div>
        </PanelFooter>
      )}
    </Panel>
  )
}
