import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterButton, type FilterField, type LockedFilter } from '@/components/custom/filter-button'
import { LineChart } from '@/components/custom/line-chart'
import {
  DetailOptionsDrawer,
  OptionsDrawerTrigger,
  useDetailOptions,
} from '@/components/custom/options-drawer'
import {
  createLoggedInUsersColumns,
  LOGGED_IN_USERS_COLUMN_CONFIG,
  LOGGED_IN_USERS_CONTEXT,
  LOGGED_IN_USERS_DEFAULT_API_COLUMNS,
  LOGGED_IN_USERS_DEFAULT_HIDDEN_COLUMNS,
  transformLoggedInUserData,
} from '@/components/data-table-columns/logged-in-users-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { ChartSkeleton, PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useChartData } from '@/hooks/use-chart-data'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { mergeChartResponses } from '@/lib/chart-utils'
import { WordPress } from '@/lib/wordpress'
import { getLoggedInUsersBatchQueryOptions } from '@/services/visitor-insight/get-logged-in-users-batch'

const PER_PAGE = 25

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

export const Route = createLazyFileRoute('/(visitor-insights)/logged-in-users')({
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
    removeFilter: handleRemoveFilter,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'lastVisit', desc: true }],
    onPageReset: () => setPage(1),
  })
  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Options drawer
  const options = useDetailOptions({ filterGroup: 'visitors' })

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

  // Define locked filter for this report (logged-in users only)
  const lockedFilters = useMemo<LockedFilter[]>(
    () => [
      {
        id: 'logged_in-locked',
        label: __('User Type', 'wp-statistics'),
        operator: __('is', 'wp-statistics'),
        value: __('Logged-in', 'wp-statistics'),
      },
    ],
    []
  )

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  // Fetch all data in a single batch request
  const {
    data: batchResponse,
    isLoading: isBatchLoading,
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
      columns: LOGGED_IN_USERS_DEFAULT_API_COLUMNS,
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Extract individual responses from batch
  const usersResponse = batchResponse?.data?.items?.logged_in_users
  const loggedInTrendsResponse = batchResponse?.data?.items?.logged_in_trends
  const anonymousTrendsResponse = batchResponse?.data?.items?.anonymous_trends

  // Use the preferences hook for column management
  const {
    columnOrder,
    initialColumnVisibility,
    handleColumnVisibilityChange,
    handleColumnOrderChange,
    handleColumnPreferencesReset,
  } = useDataTablePreferences({
    context: LOGGED_IN_USERS_CONTEXT,
    columns,
    defaultHiddenColumns: LOGGED_IN_USERS_DEFAULT_HIDDEN_COLUMNS,
    defaultApiColumns: LOGGED_IN_USERS_DEFAULT_API_COLUMNS,
    columnConfig: LOGGED_IN_USERS_COLUMN_CONFIG,
    sorting,
    defaultSortColumn: 'lastVisit',
    preferencesFromApi: usersResponse?.meta?.preferences?.columns,
    hasApiResponse: !!usersResponse,
  })

  // Transform users data
  const tableData = useMemo(() => {
    if (!usersResponse?.data?.rows) return []
    return usersResponse.data.rows.map(transformLoggedInUserData)
  }, [usersResponse])

  // Get pagination info
  const totalRows = usersResponse?.meta?.total_pages ? usersResponse.meta.total_pages * PER_PAGE : tableData.length
  const totalPages = usersResponse?.meta?.total_pages || Math.ceil(totalRows / PER_PAGE) || 1

  // Merge logged-in and anonymous trends into single chart response
  const mergedChartResponse = useMemo(
    () =>
      mergeChartResponses(
        [loggedInTrendsResponse, anonymousTrendsResponse],
        [{ visitors: 'userVisitors' }, { visitors: 'anonymousVisitors' }]
      ),
    [loggedInTrendsResponse, anonymousTrendsResponse]
  )

  // Transform chart data using shared hook
  const { data: trafficTrendsData, metrics: trafficTrendsMetrics } = useChartData(mergedChartResponse, {
    metrics: [
      { key: 'userVisitors', label: __('User Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'anonymousVisitors', label: __('Anonymous Visitors', 'wp-statistics'), color: 'var(--chart-2)' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  const handlePageChange = useCallback(
    (newPage: number) => {
      setPage(newPage)
    },
    [setPage]
  )

  const isChartLoading = isBatchFetching
  const showSkeleton = isBatchLoading && !batchResponse

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Logged-in Users', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && isInitialized && (
            <FilterButton
              fields={filterFields}
              appliedFilters={appliedFilters || []}
              onApplyFilters={handleApplyFilters}
              filterGroup="visitors"
              lockedFilters={lockedFilters}
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

      {/* Options Drawer */}
      <DetailOptionsDrawer
        config={{ filterGroup: 'visitors' }}
        isOpen={options.isOpen}
        setIsOpen={options.setIsOpen}
      />

      <div className="p-2 grid gap-3">
        <NoticeContainer currentRoute="logged-in-users" />

        {isBatchError ? (
          <div className="p-4 text-center">
            <ErrorMessage message={__('Failed to load logged-in users', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{batchError?.message}</p>
          </div>
        ) : showSkeleton ? (
          <div className="space-y-4">
            <PanelSkeleton titleWidth="w-32">
              <ChartSkeleton height={256} showTitle={false} />
            </PanelSkeleton>
            <PanelSkeleton titleWidth="w-28">
              <TableSkeleton rows={10} columns={8} />
            </PanelSkeleton>
          </div>
        ) : (
          <>
            <LineChart
              title={__('Traffic Trends', 'wp-statistics')}
              data={trafficTrendsData}
              metrics={trafficTrendsMetrics}
              showPreviousPeriod={isCompareEnabled}
              timeframe={timeframe}
              onTimeframeChange={setTimeframe}
              isLoading={isChartLoading}
              borderless
              compareDateTo={apiDateParams.previous_date_to}
              dateTo={apiDateParams.date_to}
            />

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
              borderless
            />
          </>
        )}
      </div>
    </div>
  )
}
