import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { SortingState, VisibilityState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import {
  createTopAuthorsColumns,
  transformTopAuthorData,
  TOP_AUTHORS_COLUMN_CONFIG,
  TOP_AUTHORS_CONTEXT,
  TOP_AUTHORS_DEFAULT_API_COLUMNS,
  TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS,
} from '@/components/data-table-columns/top-authors-columns'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import {
  clearCachedColumns,
  computeApiColumns,
  getCachedApiColumns,
  getCachedVisibility,
  getCachedVisibleColumns,
  getVisibleColumnsForSave,
  setCachedColumns,
} from '@/lib/column-utils'
import { WordPress } from '@/lib/wordpress'
import {
  computeFullVisibility,
  parseColumnPreferences,
  resetUserPreferences,
  saveUserPreferences,
} from '@/services/user-preferences'
import { getTopAuthorsQueryOptions } from '@/services/content-analytics/get-top-authors'

const PER_PAGE = 20

// Get cached column order from localStorage
const getCachedColumnOrder = (): string[] => {
  return getCachedVisibleColumns(TOP_AUTHORS_CONTEXT) || []
}

export const Route = createLazyFileRoute('/(content-analytics)/top-authors')({
  component: RouteComponent,
})

function RouteComponent() {
  const { post_type } = Route.useSearch()

  // Use global filters context for date range (filters are managed locally on this page)
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

  const [sorting, setSorting] = useState<SortingState>([{ id: 'views', desc: true }])
  const [defaultFilterRemoved, setDefaultFilterRemoved] = useState(false)

  const wp = WordPress.getInstance()
  const columns = useMemo(() => createTopAuthorsColumns(), [])

  // Available filters: Post Type
  const AVAILABLE_FILTERS = ['post_type']

  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('content').filter((field) => AVAILABLE_FILTERS.includes(field.name)) as FilterField[]
  }, [wp])

  // Don't inherit filters from global state - this page starts fresh
  // Users can add filters via the filter button, which will be stored in local state
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

  // Build default post_type filter (memoized to prevent unnecessary re-renders)
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

  // Filters to use for API requests and display (memoized to ensure stable reference for React Query)
  const filtersForApi = useMemo(() => {
    return showDefaultFilter ? [...normalizedFilters, defaultPostTypeFilter] : normalizedFilters
  }, [showDefaultFilter, normalizedFilters, defaultPostTypeFilter])

  const filtersForDisplay = filtersForApi

  // Handle filter removal - detect when post_type filter is intentionally removed
  const handleTopAuthorsRemoveFilter = useCallback(
    (filterId: string) => {
      if (filterId === 'post_type-top-authors-default') {
        setDefaultFilterRemoved(true)
      } else {
        // Remove from local filters
        setLocalFilters((prev) => (prev || []).filter((f) => f.id !== filterId))
      }
    },
    []
  )

  // Handle filter application - store in local state instead of global
  const handleTopAuthorsApplyFilters = useCallback(
    (newFilters: typeof appliedFilters) => {
      const hasPostTypeFilter = (newFilters || []).some((f) => f.id.startsWith('post_type'))
      if (hasPostTypeFilter) {
        setDefaultFilterRemoved(false)
      }
      setLocalFilters(newFilters)
      setPage(1) // Reset to first page when filters change
    },
    [setPage]
  )

  // Handle date range updates from DateRangePicker
  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  // Determine sort parameters from sorting state
  const orderBy = sorting.length > 0 ? sorting[0].id : 'views'
  const order = sorting.length > 0 && sorting[0].desc ? 'desc' : 'asc'

  // Get all hideable column IDs from the columns definition
  const allColumnIds = useMemo(() => {
    return columns
      .filter((col) => col.enableHiding !== false)
      .map((col) => ('accessorKey' in col ? (col.accessorKey as string) : ''))
      .filter(Boolean)
  }, [columns])

  // Track column order state
  const [columnOrder, setColumnOrder] = useState<string[]>(() => getCachedColumnOrder())

  // Track API columns for query optimization
  const [apiColumns, setApiColumns] = useState<string[]>(() => {
    return getCachedApiColumns(allColumnIds, TOP_AUTHORS_COLUMN_CONFIG) || TOP_AUTHORS_DEFAULT_API_COLUMNS
  })

  // Track if preferences have been applied
  const hasAppliedPrefs = useRef(false)
  const computedVisibilityRef = useRef<VisibilityState | null>(null)
  const computedColumnOrderRef = useRef<string[] | null>(null)
  const currentVisibilityRef = useRef<VisibilityState>({})
  const hasInitialPrefSync = useRef(false)
  const emptyVisibilityRef = useRef<VisibilityState>({})

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
      columns: apiColumns,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Compute initial visibility only once when API returns preferences
  const initialColumnVisibility = useMemo(() => {
    if (hasAppliedPrefs.current && computedVisibilityRef.current) {
      return computedVisibilityRef.current
    }

    if (!response?.data) {
      const cachedVisibility = getCachedVisibility(TOP_AUTHORS_CONTEXT, allColumnIds)
      if (cachedVisibility) {
        return cachedVisibility
      }
      return emptyVisibilityRef.current
    }

    const prefs = response.data.meta?.preferences?.columns

    if (!prefs || prefs.length === 0) {
      const defaultVisibility = TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS.reduce(
        (acc, col) => ({ ...acc, [col]: false }),
        {} as VisibilityState
      )
      hasAppliedPrefs.current = true
      computedVisibilityRef.current = defaultVisibility
      currentVisibilityRef.current = defaultVisibility
      computedColumnOrderRef.current = []
      return defaultVisibility
    }

    const { visibleColumnsSet, columnOrder: newOrder } = parseColumnPreferences(prefs)
    const visibility = computeFullVisibility(visibleColumnsSet, allColumnIds)

    hasAppliedPrefs.current = true
    computedVisibilityRef.current = visibility
    currentVisibilityRef.current = visibility
    computedColumnOrderRef.current = newOrder

    return visibility
  }, [response?.data, allColumnIds])

  // Sync column order when preferences are computed
  useEffect(() => {
    if (hasAppliedPrefs.current && computedVisibilityRef.current && !hasInitialPrefSync.current) {
      hasInitialPrefSync.current = true
      if (computedColumnOrderRef.current && computedColumnOrderRef.current.length > 0) {
        setColumnOrder(computedColumnOrderRef.current)
      }
    }
  }, [initialColumnVisibility])

  // Helper to compare two arrays for equality
  const arraysEqual = useCallback((a: string[], b: string[]): boolean => {
    if (a.length !== b.length) return false
    return a.every((val, idx) => val === b[idx])
  }, [])

  // Handle column visibility changes
  const handleColumnVisibilityChange = useCallback(
    (visibility: VisibilityState) => {
      currentVisibilityRef.current = visibility
      const visibleColumns = getVisibleColumnsForSave(visibility, columnOrder, allColumnIds)
      saveUserPreferences({ context: TOP_AUTHORS_CONTEXT, columns: visibleColumns })
      setCachedColumns(TOP_AUTHORS_CONTEXT, visibleColumns)
      const currentSortColumn = sorting.length > 0 ? sorting[0].id : 'views'
      const newApiColumns = computeApiColumns(visibility, allColumnIds, TOP_AUTHORS_COLUMN_CONFIG, currentSortColumn)
      setApiColumns((prev) => (arraysEqual(prev, newApiColumns) ? prev : newApiColumns))
    },
    [columnOrder, sorting, allColumnIds, arraysEqual]
  )

  // Handle column order changes
  const handleColumnOrderChange = useCallback(
    (order: string[]) => {
      setColumnOrder(order)
      const visibleColumns = getVisibleColumnsForSave(currentVisibilityRef.current, order, allColumnIds)
      saveUserPreferences({ context: TOP_AUTHORS_CONTEXT, columns: visibleColumns })
      setCachedColumns(TOP_AUTHORS_CONTEXT, visibleColumns)
    },
    [allColumnIds]
  )

  // Handle reset to default
  const handleColumnPreferencesReset = useCallback(() => {
    setColumnOrder([])
    const defaultVisibility = TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS.reduce(
      (acc, col) => ({ ...acc, [col]: false }),
      {} as VisibilityState
    )
    computedVisibilityRef.current = defaultVisibility
    currentVisibilityRef.current = defaultVisibility
    setApiColumns((prev) =>
      arraysEqual(prev, TOP_AUTHORS_DEFAULT_API_COLUMNS) ? prev : TOP_AUTHORS_DEFAULT_API_COLUMNS
    )
    resetUserPreferences({ context: TOP_AUTHORS_CONTEXT })
    clearCachedColumns(TOP_AUTHORS_CONTEXT)
  }, [arraysEqual])

  // Transform API data to component interface
  const tableData = useMemo(() => {
    if (!response?.data?.data?.rows) return []
    return response.data.data.rows.map(transformTopAuthorData)
  }, [response])

  // Get pagination info from meta
  const totalRows = response?.data?.meta?.total_rows ?? 0
  const totalPages = response?.data?.meta?.total_pages || Math.ceil(totalRows / PER_PAGE) || 1

  // Handle sorting changes
  const handleSortingChange = useCallback(
    (newSorting: SortingState) => {
      setSorting(newSorting)
      setPage(1)
    },
    [setPage]
  )

  // Handle page changes
  const handlePageChange = useCallback(
    (newPage: number) => {
      setPage(newPage)
    },
    [setPage]
  )

  // Loading states
  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      {/* Header row with title and filter button */}
      <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
        <h1 className="text-xl font-semibold text-neutral-800">{__('Top Authors', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && isInitialized && (
            <FilterButton
              fields={filterFields}
              appliedFilters={filtersForDisplay}
              onApplyFilters={handleTopAuthorsApplyFilters}
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

      <div className="p-2">
        <NoticeContainer className="mb-2" currentRoute="top-authors" />
        {/* Applied filters row */}
        {filtersForDisplay.length > 0 && (
          <FilterBar filters={filtersForDisplay} onRemoveFilter={handleTopAuthorsRemoveFilter} className="mb-2" />
        )}

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
            showColumnManagement={true}
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
          />
        )}
      </div>
    </div>
  )
}
