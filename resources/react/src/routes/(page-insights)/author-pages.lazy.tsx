import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef, SortingState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { decodeText, formatCompactNumber } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getAuthorPagesQueryOptions, type AuthorPageRecord } from '@/services/page-insight/get-author-pages'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(page-insights)/author-pages')({
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

  const [sorting, setSorting] = useState<SortingState>([{ id: 'views', desc: true }])

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

  const orderBy = sorting.length > 0 ? sorting[0].id : 'views'
  const order = sorting.length > 0 && sorting[0].desc ? 'desc' : 'asc'

  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getAuthorPagesQueryOptions({
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

  const columns = useMemo<ColumnDef<AuthorPageRecord>[]>(
    () => [
      {
        accessorKey: 'page_title',
        header: __('Author Page', 'wp-statistics'),
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
        accessorKey: 'views',
        header: __('Views', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => (
          <div className="text-right">
            <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatCompactNumber(Number(row.original.views))}</span>
          </div>
        ),
      },
      {
        accessorKey: 'visitors',
        header: __('Visitors', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => (
          <div className="text-right">
            <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatCompactNumber(Number(row.original.visitors))}</span>
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

  const handleSortingChange = useCallback(
    (newSorting: SortingState) => {
      setSorting(newSorting)
      setPage(1)
    },
    [setPage]
  )

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
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Author Pages', 'wp-statistics')}</h1>
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
        <NoticeContainer className="mb-2" currentRoute="author-pages" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load author pages', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-28">
            <TableSkeleton rows={10} columns={3} />
          </PanelSkeleton>
        ) : (
          <DataTable
            columns={columns}
            data={tableData}
            title={__('Author Pages', 'wp-statistics')}
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
            emptyStateMessage={__('No author pages found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
          />
        )}
      </div>
    </div>
  )
}
