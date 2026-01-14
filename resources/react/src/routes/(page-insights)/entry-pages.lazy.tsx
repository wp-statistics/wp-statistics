import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { decodeText, formatCompactNumber, formatDecimal } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getEntryPagesQueryOptions, type EntryPageRecord } from '@/services/page-insight/get-entry-pages'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(page-insights)/entry-pages')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    filters: appliedFilters,
    page,
    setPage,
    setDateRange,
    applyFilters: handleApplyFilters,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'sessions', desc: true }],
    onPageReset: () => setPage(1),
  })

  const wp = WordPress.getInstance()

  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('views') as FilterField[]
  }, [wp])

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getEntryPagesQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order,
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: appliedFilters || [],
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const columns = useMemo<ColumnDef<EntryPageRecord>[]>(
    () => [
      {
        accessorKey: 'page_title',
        header: __('Page', 'wp-statistics'),
        enableSorting: false,
        cell: ({ row }) => {
          const title = decodeText(row.original.page_title) || row.original.page_uri
          const uri = row.original.page_uri
          return (
            <div className="flex flex-col gap-0.5">
              <span className="text-xs font-medium text-neutral-700 line-clamp-1">{title}</span>
              <span className="text-xs text-muted-foreground line-clamp-1">{uri}</span>
            </div>
          )
        },
      },
      {
        accessorKey: 'page_type',
        header: __('Type', 'wp-statistics'),
        enableSorting: false,
        cell: ({ row }) => (
          <span className="text-sm text-muted-foreground capitalize">{row.original.page_type || '-'}</span>
        ),
      },
      {
        accessorKey: 'sessions',
        header: __('Sessions', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => (
          <div className="text-right">
            <span className="text-xs font-medium text-neutral-700 tabular-nums">
              {formatCompactNumber(Number(row.original.sessions))}
            </span>
          </div>
        ),
      },
      {
        accessorKey: 'bounce_rate',
        header: __('Bounce Rate', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => (
          <div className="text-right">
            <span className="text-xs font-medium text-neutral-700 tabular-nums">
              {formatDecimal(Number(row.original.bounce_rate) || 0)}%
            </span>
          </div>
        ),
      },
    ],
    []
  )

  const tableData = useMemo(() => {
    if (!response?.data?.data?.rows) return []
    return response.data.data.rows
  }, [response])

  const totalRows = response?.data?.meta?.total_rows ?? 0
  const totalPages = response?.data?.meta?.total_pages || Math.ceil(totalRows / PER_PAGE) || 1

  const handlePageChange = useCallback(
    (newPage: number) => {
      setPage(newPage)
    },
    [setPage]
  )

  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Entry Pages', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && isInitialized && (
            <FilterButton
              fields={filterFields}
              appliedFilters={appliedFilters || []}
              onApplyFilters={handleApplyFilters}
              filterGroup="views"
            />
          )}
          <DateRangePicker
            initialDateFrom={dateFrom}
            initialDateTo={dateTo}
            initialCompareFrom={compareDateFrom}
            initialCompareTo={compareDateTo}
            initialPeriod={period}
            onUpdate={handleDateRangeUpdate}
            showCompare={true}
            align="end"
          />
        </div>
      </div>

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="entry-pages" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load entry pages', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-28">
            <TableSkeleton rows={10} columns={4} />
          </PanelSkeleton>
        ) : (
          <DataTable
            columns={columns}
            data={tableData}
            title={__('Entry Pages', 'wp-statistics')}
            sorting={sorting}
            onSortingChange={handleSortingChange}
            manualSorting={true}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={totalRows}
            rowLimit={PER_PAGE}
            showColumnManagement={false}
            showPagination={true}
            isFetching={isFetching}
            emptyStateMessage={__('No entry pages found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
          />
        )}
      </div>
    </div>
  )
}
