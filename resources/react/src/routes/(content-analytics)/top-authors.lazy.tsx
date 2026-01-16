import type { Table } from '@tanstack/react-table'
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import {
  OptionsDrawerTrigger,
  TableOptionsDrawer,
  useTableOptions,
} from '@/components/custom/options-drawer'
import {
  createTopAuthorsColumns,
  type TopAuthor,
  TOP_AUTHORS_COLUMN_CONFIG,
  TOP_AUTHORS_CONTEXT,
  TOP_AUTHORS_DEFAULT_API_COLUMNS,
  TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS,
  transformTopAuthorData,
} from '@/components/data-table-columns/top-authors-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { WordPress } from '@/lib/wordpress'
import { getTopAuthorsQueryOptions } from '@/services/content-analytics/get-top-authors'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(content-analytics)/top-authors')({
  component: RouteComponent,
})

function RouteComponent() {
  const { post_type } = Route.useSearch()

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
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'views', desc: true }],
    onPageReset: () => setPage(1),
  })
  const [defaultFilterRemoved, setDefaultFilterRemoved] = useState(false)

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<TopAuthor> | null>(null)

  const wp = WordPress.getInstance()
  const columns = useMemo(() => createTopAuthorsColumns(), [])

  // Available filters: Post Type
  const AVAILABLE_FILTERS = ['post_type']

  const filterFields = useMemo<FilterField[]>(() => {
    return wp
      .getFilterFieldsByGroup('content')
      .filter((field) => AVAILABLE_FILTERS.includes(field.name)) as FilterField[]
  }, [wp])

  // Don't inherit filters from global state - this page starts fresh
  const [localFilters, setLocalFilters] = useState<typeof appliedFilters>([])

  // Use local filters instead of global filters for this page
  const normalizedFilters = useMemo(() => {
    return (localFilters || []).filter((f) => {
      const filterName = f.id.split('-')[0]
      return AVAILABLE_FILTERS.includes(filterName)
    })
  }, [localFilters])

  // Check if user has applied a post_type filter (overriding default)
  const hasUserPostTypeFilter = normalizedFilters.some((f) => f.id.startsWith('post_type'))

  // Build default post_type filter
  const defaultPostTypeFilter = useMemo(() => {
    const postTypeField = filterFields.find((f) => f.name === 'post_type')
    const defaultPostType = post_type || 'post'
    const postTypeOption = postTypeField?.options?.find((o) => o.value === defaultPostType)
    return {
      id: 'post_type-top-authors-default',
      label: postTypeField?.label || __('Post Type', 'wp-statistics'),
      operator: '=',
      rawOperator: 'is',
      value: postTypeOption?.label || defaultPostType,
      rawValue: defaultPostType,
    }
  }, [filterFields, post_type])

  // Determine if we should show the default filter
  const showDefaultFilter = !hasUserPostTypeFilter && !defaultFilterRemoved

  // Filters to use for API requests and display
  const filtersForApi = useMemo(() => {
    return showDefaultFilter ? [...normalizedFilters, defaultPostTypeFilter] : normalizedFilters
  }, [showDefaultFilter, normalizedFilters, defaultPostTypeFilter])

  const filtersForDisplay = filtersForApi

  // Handle filter application
  const handleTopAuthorsApplyFilters = useCallback(
    (newFilters: typeof appliedFilters) => {
      const hasPostTypeFilter = (newFilters || []).some((f) => f.id.startsWith('post_type'))
      if (hasPostTypeFilter) {
        setDefaultFilterRemoved(false)
      }
      setLocalFilters(newFilters)
      setPage(1)
    },
    [setPage]
  )

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  // Fetch data from API
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getTopAuthorsQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      filters: filtersForApi,
      context: TOP_AUTHORS_CONTEXT,
      columns: TOP_AUTHORS_DEFAULT_API_COLUMNS,
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
    context: TOP_AUTHORS_CONTEXT,
    columns,
    defaultHiddenColumns: TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: TOP_AUTHORS_DEFAULT_API_COLUMNS,
    columnConfig: TOP_AUTHORS_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'views',
    preferencesFromApi: response?.data?.meta?.preferences?.columns,
    hasApiResponse: !!response?.data,
  })

  // Options drawer with column management
  const options = useTableOptions({
    filterGroup: 'content',
    table: tableRef.current,
    defaultHiddenColumns: TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onReset: handleColumnPreferencesReset,
  })

  // Transform API data to component interface
  const tableData = useMemo(() => {
    if (!response?.data?.data?.rows) return []
    return response.data.data.rows.map(transformTopAuthorData)
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
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Top Authors', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && isInitialized && (
            <FilterButton
              fields={filterFields}
              appliedFilters={filtersForDisplay}
              onApplyFilters={handleTopAuthorsApplyFilters}
              filterGroup="content"
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
          <OptionsDrawerTrigger {...options.triggerProps} />
        </div>
      </div>

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer
        config={{
          filterGroup: 'content',
          table: tableRef.current,
          defaultHiddenColumns: TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS,
          onColumnVisibilityChange: handleColumnVisibilityChange,
          onColumnOrderChange: handleColumnOrderChange,
          onReset: handleColumnPreferencesReset,
        }}
        isOpen={options.isOpen}
        setIsOpen={options.setIsOpen}
      />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="top-authors" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load top authors', 'wp-statistics')} />
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
            showColumnManagement={false}
            showPagination={true}
            isFetching={isFetching}
            hiddenColumns={TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            emptyStateMessage={__('No authors found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
            tableRef={tableRef}
          />
        )}
      </div>
    </div>
  )
}
