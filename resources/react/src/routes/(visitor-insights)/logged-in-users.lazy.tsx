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
import { LineChart } from '@/components/custom/line-chart'
import {
  createLoggedInUsersColumns,
  LOGGED_IN_USERS_COLUMN_CONFIG,
  LOGGED_IN_USERS_CONTEXT,
  LOGGED_IN_USERS_DEFAULT_API_COLUMNS,
  LOGGED_IN_USERS_DEFAULT_HIDDEN_COLUMNS,
  transformLoggedInUserData,
} from '@/components/data-table-columns/logged-in-users-columns'
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
import { formatDecimal } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import {
  computeFullVisibility,
  parseColumnPreferences,
  resetUserPreferences,
  saveUserPreferences,
} from '@/services/user-preferences'
import { getLoggedInUsersBatchQueryOptions } from '@/services/visitor-insight/get-logged-in-users-batch'

const PER_PAGE = 50

// Get cached column order from localStorage (same as visible columns order)
const getCachedColumnOrder = (): string[] => {
  return getCachedVisibleColumns(LOGGED_IN_USERS_CONTEXT) || []
}

export const Route = createLazyFileRoute('/(visitor-insights)/logged-in-users')({
  component: RouteComponent,
})

interface TrafficTrendItem {
  date: string
  userVisitors: number
  userVisitorsPrevious: number
  anonymousVisitors: number
  anonymousVisitorsPrevious: number
  [key: string]: string | number
}

// Determine group_by based on timeframe
const getGroupBy = (timeframe: 'daily' | 'weekly' | 'monthly'): 'date' | 'week' | 'month' => {
  switch (timeframe) {
    case 'weekly':
      return 'week'
    case 'monthly':
      return 'month'
    default:
      return 'date'
  }
}

function RouteComponent() {
  // Use global filters context for date range and filters (hybrid URL + preferences)
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    filters: appliedFilters,
    page,
    setPage,
    setDateRange,
    applyFilters: handleApplyFilters,
    removeFilter: handleRemoveFilter,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const [sorting, setSorting] = useState<SortingState>([{ id: 'lastVisit', desc: true }])
  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const columns = useMemo(
    () =>
      createLoggedInUsersColumns({
        pluginUrl,
        trackLoggedInEnabled: true, // Always true for logged-in users page
        hashEnabled: wp.isHashEnabled(),
      }),
    [pluginUrl, wp]
  )

  // Get filter fields for 'visitors' group from localized data
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('visitors') as FilterField[]
  }, [wp])

  // Handle date range updates from DateRangePicker
  const handleDateRangeUpdate = useCallback((values: { range: DateRange; rangeCompare?: DateRange }) => {
    setDateRange(values.range, values.rangeCompare)
  }, [setDateRange])

  // Determine sort parameters from sorting state
  const orderBy = sorting.length > 0 ? sorting[0].id : 'lastVisit'
  const order = sorting.length > 0 && sorting[0].desc ? 'desc' : 'asc'

  // Get all hideable column IDs from the columns definition
  const allColumnIds = useMemo(() => {
    return columns.filter((col) => col.enableHiding !== false).map((col) => col.accessorKey as string)
  }, [columns])

  // Track API columns for query optimization (state so changes trigger refetch)
  // Initialize from cache if available, otherwise use all columns
  const [apiColumns, setApiColumns] = useState<string[]>(() => {
    return getCachedApiColumns(allColumnIds, LOGGED_IN_USERS_COLUMN_CONFIG) || LOGGED_IN_USERS_DEFAULT_API_COLUMNS
  })

  // Fetch all data in a single batch request (only when filters are initialized)
  const {
    data: batchResponse,
    isFetching: isBatchFetching,
    isError: isBatchError,
    error: batchError,
  } = useQuery({
    ...getLoggedInUsersBatchQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
      group_by: getGroupBy(timeframe),
      filters: appliedFilters || [],
      context: LOGGED_IN_USERS_CONTEXT,
      columns: apiColumns,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Extract individual responses from batch
  const usersResponse = batchResponse?.data?.items?.logged_in_users
  const loggedInTrendsResponse = batchResponse?.data?.items?.logged_in_trends
  const anonymousTrendsResponse = batchResponse?.data?.items?.anonymous_trends

  // Track column order state
  const [columnOrder, setColumnOrder] = useState<string[]>(() => getCachedColumnOrder())

  // Track if preferences have been applied (to prevent re-computation on subsequent API responses)
  const hasAppliedPrefs = useRef(false)
  const computedVisibilityRef = useRef<VisibilityState | null>(null)
  const computedColumnOrderRef = useRef<string[] | null>(null)

  // Track if initial preference sync has been done (to prevent unnecessary refetches)
  const hasInitialPrefSync = useRef(false)

  // Stable empty visibility state to avoid creating new objects on each render
  const emptyVisibilityRef = useRef<VisibilityState>({})

  // Compute initial visibility only once when API returns preferences
  const initialColumnVisibility = useMemo(() => {
    // If we've already computed visibility, return the cached value
    if (hasAppliedPrefs.current && computedVisibilityRef.current) {
      return computedVisibilityRef.current
    }

    // Use cached visibility from localStorage while waiting for API response
    // This prevents flash of all columns before preferences load
    if (!usersResponse) {
      const cachedVisibility = getCachedVisibility(LOGGED_IN_USERS_CONTEXT, allColumnIds)
      if (cachedVisibility) {
        return cachedVisibility
      }
      return emptyVisibilityRef.current
    }

    const prefs = usersResponse.meta?.preferences?.columns

    // If no preferences in API response (new user or reset), use defaults
    if (!prefs || prefs.length === 0) {
      const defaultVisibility = LOGGED_IN_USERS_DEFAULT_HIDDEN_COLUMNS.reduce(
        (acc, col) => ({ ...acc, [col]: false }),
        {} as VisibilityState
      )
      hasAppliedPrefs.current = true
      computedVisibilityRef.current = defaultVisibility
      computedColumnOrderRef.current = []
      return defaultVisibility
    }

    // Parse preferences and compute full visibility
    const { visibleColumnsSet, columnOrder: newOrder } = parseColumnPreferences(prefs)
    const visibility = computeFullVisibility(visibleColumnsSet, allColumnIds)

    // Mark as applied and cache the result
    hasAppliedPrefs.current = true
    computedVisibilityRef.current = visibility
    computedColumnOrderRef.current = newOrder

    return visibility
     
  }, [usersResponse, allColumnIds])

  // Sync column order when preferences are computed (only once on initial load)
  useEffect(() => {
    if (hasAppliedPrefs.current && computedVisibilityRef.current && !hasInitialPrefSync.current) {
      hasInitialPrefSync.current = true
      // Sync column order from preferences
      if (computedColumnOrderRef.current && computedColumnOrderRef.current.length > 0) {
        setColumnOrder(computedColumnOrderRef.current)
      }
      // Note: We don't update apiColumns here on initial load because:
      // 1. DEFAULT_API_COLUMNS already includes all columns
      // 2. The initial query already fetched with all columns
      // 3. API column optimization only happens when user changes visibility
    }
  }, [initialColumnVisibility])

  // Track current visibility for save operations (updated via callback)
  const currentVisibilityRef = useRef<VisibilityState>(initialColumnVisibility)

  // Helper to compare two arrays for equality (same elements, same order)
  const arraysEqual = useCallback((a: string[], b: string[]): boolean => {
    if (a.length !== b.length) return false
    return a.every((val, idx) => val === b[idx])
  }, [])

  // Handle column visibility changes (for persistence and query optimization)
  const handleColumnVisibilityChange = useCallback(
    (visibility: VisibilityState) => {
      currentVisibilityRef.current = visibility
      // Use local function that properly handles all visible columns
      const visibleColumns = getVisibleColumnsForSave(visibility, columnOrder, allColumnIds)
      saveUserPreferences({ context: LOGGED_IN_USERS_CONTEXT, columns: visibleColumns })
      // Cache visible columns in localStorage for next page load
      setCachedColumns(LOGGED_IN_USERS_CONTEXT, visibleColumns)
      // Update API columns for optimized queries (include sort column)
      // Use functional update to avoid unnecessary refetches when columns haven't changed
      const currentSortColumn = sorting.length > 0 ? sorting[0].id : 'lastVisit'
      const newApiColumns = computeApiColumns(visibility, allColumnIds, LOGGED_IN_USERS_COLUMN_CONFIG, currentSortColumn)
      setApiColumns((prev) => (arraysEqual(prev, newApiColumns) ? prev : newApiColumns))
    },
    [columnOrder, sorting, allColumnIds, arraysEqual]
  )

  // Handle column order changes
  const handleColumnOrderChange = useCallback(
    (order: string[]) => {
      setColumnOrder(order)
      // Use local function that properly handles all visible columns
      const visibleColumns = getVisibleColumnsForSave(currentVisibilityRef.current, order, allColumnIds)
      saveUserPreferences({ context: LOGGED_IN_USERS_CONTEXT, columns: visibleColumns })
      // Cache visible columns in localStorage for next page load
      setCachedColumns(LOGGED_IN_USERS_CONTEXT, visibleColumns)
    },
    [allColumnIds]
  )

  // Handle reset to default
  const handleColumnPreferencesReset = useCallback(() => {
    setColumnOrder([])
    const defaultVisibility = LOGGED_IN_USERS_DEFAULT_HIDDEN_COLUMNS.reduce(
      (acc, col) => ({ ...acc, [col]: false }),
      {} as VisibilityState
    )
    computedVisibilityRef.current = defaultVisibility
    currentVisibilityRef.current = defaultVisibility
    // Reset API columns to default (use functional update to avoid unnecessary refetch)
    setApiColumns((prev) => (arraysEqual(prev, LOGGED_IN_USERS_DEFAULT_API_COLUMNS) ? prev : LOGGED_IN_USERS_DEFAULT_API_COLUMNS))
    resetUserPreferences({ context: LOGGED_IN_USERS_CONTEXT })
    // Clear localStorage cache
    clearCachedColumns(LOGGED_IN_USERS_CONTEXT)
  }, [arraysEqual])

  // Transform users data
  const tableData = useMemo(() => {
    if (!usersResponse?.data?.rows) return []
    return usersResponse.data.rows.map(transformLoggedInUserData)
  }, [usersResponse])

  // Get pagination info
  const totalRows = usersResponse?.meta?.total_pages ? usersResponse.meta.total_pages * PER_PAGE : tableData.length
  const totalPages = usersResponse?.meta?.total_pages || Math.ceil(totalRows / PER_PAGE) || 1

  // Combine traffic trends data from chart format responses
  const trafficTrendsData = useMemo<TrafficTrendItem[]>(() => {
    // Chart format has labels[] and datasets[{key, label, data, comparison?}]
    const loggedInLabels = loggedInTrendsResponse?.labels || []
    const loggedInDatasets = loggedInTrendsResponse?.datasets || []
    const anonymousLabels = anonymousTrendsResponse?.labels || []
    const anonymousDatasets = anonymousTrendsResponse?.datasets || []

    // Get dataset by key
    const getDataset = (datasets: typeof loggedInDatasets, key: string) =>
      datasets.find((d) => d.key === key)?.data || []

    // Get logged-in visitors data
    const loggedInVisitors = getDataset(loggedInDatasets, 'visitors')
    const loggedInVisitorsPrevious = getDataset(loggedInDatasets, 'visitors_previous')

    // Get anonymous visitors data
    const anonymousVisitors = getDataset(anonymousDatasets, 'visitors')
    const anonymousVisitorsPrevious = getDataset(anonymousDatasets, 'visitors_previous')

    // Use logged-in labels as primary (both should have same labels)
    const labels = loggedInLabels.length > 0 ? loggedInLabels : anonymousLabels

    // Build combined data array
    return labels.map((date, index) => ({
      date,
      userVisitors: Number(loggedInVisitors[index]) || 0,
      userVisitorsPrevious: Number(loggedInVisitorsPrevious[index]) || 0,
      anonymousVisitors: Number(anonymousVisitors[index]) || 0,
      anonymousVisitorsPrevious: Number(anonymousVisitorsPrevious[index]) || 0,
    }))
  }, [loggedInTrendsResponse, anonymousTrendsResponse])

  // Calculate totals for metrics
  const totalUserVisitors = trafficTrendsData.reduce((sum, item) => sum + item.userVisitors, 0)
  const totalUserVisitorsPrevious = trafficTrendsData.reduce((sum, item) => sum + item.userVisitorsPrevious, 0)
  const totalAnonymousVisitors = trafficTrendsData.reduce((sum, item) => sum + item.anonymousVisitors, 0)
  const totalAnonymousVisitorsPrevious = trafficTrendsData.reduce(
    (sum, item) => sum + item.anonymousVisitorsPrevious,
    0
  )

  const trafficTrendsMetrics = [
    {
      key: 'userVisitors',
      label: __('User Visitors', 'wp-statistics'),
      color: 'var(--chart-1)',
      enabled: true,
      value: totalUserVisitors >= 1000 ? `${formatDecimal(totalUserVisitors / 1000)}k` : totalUserVisitors.toString(),
      previousValue:
        totalUserVisitorsPrevious >= 1000
          ? `${formatDecimal(totalUserVisitorsPrevious / 1000)}k`
          : totalUserVisitorsPrevious.toString(),
    },
    {
      key: 'anonymousVisitors',
      label: __('Anonymous Visitors', 'wp-statistics'),
      color: 'var(--chart-2)',
      enabled: true,
      value:
        totalAnonymousVisitors >= 1000
          ? `${formatDecimal(totalAnonymousVisitors / 1000)}k`
          : totalAnonymousVisitors.toString(),
      previousValue:
        totalAnonymousVisitorsPrevious >= 1000
          ? `${formatDecimal(totalAnonymousVisitorsPrevious / 1000)}k`
          : totalAnonymousVisitorsPrevious.toString(),
    },
  ]

  // Handle sorting changes
  const handleSortingChange = useCallback((newSorting: SortingState) => {
    setSorting(newSorting)
    setPage(1) // Reset to first page when sorting changes
  }, [setPage])

  // Handle page changes
  const handlePageChange = useCallback((newPage: number) => {
    setPage(newPage)
  }, [setPage])

  const isChartLoading = isBatchFetching

  return (
    <div className="min-w-0">
      {/* Header row with title and filter button */}
      <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
        <h1 className="text-xl font-semibold text-neutral-800">{__('Logged-in Users', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && isInitialized && (
            <FilterButton fields={filterFields} appliedFilters={appliedFilters || []} onApplyFilters={handleApplyFilters} />
          )}
          <DateRangePicker
            initialDateFrom={dateFrom}
            initialDateTo={dateTo}
            initialCompareFrom={compareDateFrom}
            initialCompareTo={compareDateTo}
            onUpdate={handleDateRangeUpdate}
            showCompare={true}
            align="end"
          />
        </div>
      </div>

      <div className="p-2 grid gap-2">
        {/* Applied filters row (separate from button) */}
        {appliedFilters && appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} />
        )}

        <LineChart
          title={__('Traffic Trends', 'wp-statistics')}
          data={trafficTrendsData}
          metrics={trafficTrendsMetrics}
          showPreviousPeriod={true}
          timeframe={timeframe}
          onTimeframeChange={setTimeframe}
          isLoading={isChartLoading}
        />

        {isBatchError ? (
          <div className="p-4 text-center">
            <ErrorMessage message={__('Failed to load logged-in users', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{batchError?.message}</p>
          </div>
        ) : (
          <DataTable
            title={__('Latest Views', 'wp-statistics')}
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
            isFetching={isBatchFetching}
            hiddenColumns={LOGGED_IN_USERS_DEFAULT_HIDDEN_COLUMNS}
            initialColumnVisibility={initialColumnVisibility}
            columnOrder={columnOrder.length > 0 ? columnOrder : undefined}
            onColumnVisibilityChange={handleColumnVisibilityChange}
            onColumnOrderChange={handleColumnOrderChange}
            onColumnPreferencesReset={handleColumnPreferencesReset}
            emptyStateMessage={__('No logged-in users found for the selected period', 'wp-statistics')}
            stickyHeader={true}
          />
        )}
      </div>
    </div>
  )
}
