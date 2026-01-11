import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { SortingState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { LineChart } from '@/components/custom/line-chart'
import { NoticeContainer } from '@/components/ui/notice-container'
import { ChartSkeleton, PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import {
  createLoggedInUsersColumns,
  LOGGED_IN_USERS_COLUMN_CONFIG,
  LOGGED_IN_USERS_CONTEXT,
  LOGGED_IN_USERS_DEFAULT_API_COLUMNS,
  LOGGED_IN_USERS_DEFAULT_HIDDEN_COLUMNS,
  transformLoggedInUserData,
} from '@/components/data-table-columns/logged-in-users-columns'
import { useDataTablePreferences } from '@/hooks/use-data-table-preferences'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { formatDecimal } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getLoggedInUsersBatchQueryOptions } from '@/services/visitor-insight/get-logged-in-users-batch'

const PER_PAGE = 50

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

interface TrafficTrendItem {
  date: string
  userVisitors: number
  userVisitorsPrevious: number
  anonymousVisitors: number
  anonymousVisitorsPrevious: number
  [key: string]: string | number
}

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

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  const orderBy = sorting.length > 0 ? sorting[0].id : 'lastVisit'
  const order = sorting.length > 0 && sorting[0].desc ? 'desc' : 'asc'

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

  // Combine traffic trends data from chart format responses
  const trafficTrendsData = useMemo<TrafficTrendItem[]>(() => {
    const loggedInLabels = loggedInTrendsResponse?.labels || []
    const loggedInDatasets = loggedInTrendsResponse?.datasets || []
    const anonymousLabels = anonymousTrendsResponse?.labels || []
    const anonymousDatasets = anonymousTrendsResponse?.datasets || []

    const getDataset = (datasets: typeof loggedInDatasets, key: string) =>
      datasets.find((d) => d.key === key)?.data || []

    const loggedInVisitors = getDataset(loggedInDatasets, 'visitors')
    const loggedInVisitorsPrevious = getDataset(loggedInDatasets, 'visitors_previous')
    const anonymousVisitors = getDataset(anonymousDatasets, 'visitors')
    const anonymousVisitorsPrevious = getDataset(anonymousDatasets, 'visitors_previous')

    const labels = loggedInLabels.length > 0 ? loggedInLabels : anonymousLabels

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

      <div className="p-2 grid gap-3">
        <NoticeContainer currentRoute="logged-in-users" />
        {appliedFilters && appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} />
        )}

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
              showPreviousPeriod={true}
              timeframe={timeframe}
              onTimeframeChange={setTimeframe}
              isLoading={isChartLoading}
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
            />
          </>
        )}
      </div>
    </div>
  )
}
