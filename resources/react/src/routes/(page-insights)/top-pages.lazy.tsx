import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { SortingState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import {
  createTopPagesColumns,
  transformTopPageData,
  TOP_PAGES_COLUMN_CONFIG,
  TOP_PAGES_CONTEXT,
  TOP_PAGES_DEFAULT_API_COLUMNS,
  TOP_PAGES_DEFAULT_HIDDEN_COLUMNS,
} from '@/components/data-table-columns/top-pages-columns'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { WordPress } from '@/lib/wordpress'
import { getTopPagesQueryOptions } from '@/services/page-insight/get-top-pages'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(page-insights)/top-pages')({
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
  const columns = useMemo(() => createTopPagesColumns(), [])

  // Content-specific filters only
  const CONTENT_FILTERS = ['page', 'resource_id', 'post_type', 'author']

  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('views').filter((field) => CONTENT_FILTERS.includes(field.name)) as FilterField[]
  }, [wp])

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  const orderBy = sorting.length > 0 ? sorting[0].id : 'views'
  const order = sorting.length > 0 && sorting[0].desc ? 'desc' : 'asc'

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getTopPagesQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: appliedFilters || [],
      context: TOP_PAGES_CONTEXT,
      columns: TOP_PAGES_DEFAULT_API_COLUMNS,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Use the preferences hook for column management
  const {
    columnOrder,
    initialColumnVisibility,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleColumnPreferencesReset,
  } = useDataTablePreferences({
    context: TOP_PAGES_CONTEXT,
    columns,
    defaultHiddenColumns: TOP_PAGES_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: TOP_PAGES_DEFAULT_API_COLUMNS,
    columnConfig: TOP_PAGES_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'views',
    preferencesFromApi: response?.data?.meta?.preferences?.columns,
    hasApiResponse: !!response?.data,
  })

  // Transform API data to component interface
  const tableData = useMemo(() => {
    if (!response?.data?.data?.rows) return []
    return response.data.data.rows.map(transformTopPageData)
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
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Top Pages', 'wp-statistics')}</h1>
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
        <NoticeContainer className="mb-2" currentRoute="top-pages" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load top pages', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={6} />
          </PanelSkeleton>
        ) : (
          <DataTable
            columns={columns}
            data={tableData}
            sorting={sorting}
            onSortingChange={handleSortingChange}
            manualSorting={true}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={totalRows}
            rowLimit={PER_PAGE}
            showColumnManagement={true}
            showPagination={true}
            isFetching={isFetching}
            hiddenColumns={TOP_PAGES_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            emptyStateMessage={__('No pages found for the selected period', 'wp-statistics')}
            stickyHeader={true}
          />
        )}
      </div>
    </div>
  )
}
