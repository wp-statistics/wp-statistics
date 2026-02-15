import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { Table } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { ErrorMessage } from '@/components/custom/error-message'
import type { LockedFilter } from '@/components/custom/filter-panel'
import { LineChart } from '@/components/custom/line-chart'
import { TableOptionsDrawer, useTableOptions } from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import {
  createLoggedInUsersColumns,
  LOGGED_IN_USERS_COLUMN_CONFIG,
  LOGGED_IN_USERS_CONTEXT,
  LOGGED_IN_USERS_DEFAULT_API_COLUMNS,
  LOGGED_IN_USERS_DEFAULT_HIDDEN_COLUMNS,
  type LoggedInUser,
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
    filters: appliedFilters,
    page,
    setPage,
    handlePageChange,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'lastVisit', desc: true }],
    onPageReset: () => setPage(1),
  })
  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Table ref for Options drawer column management
  const tableRef = useRef<Table<LoggedInUser> | null>(null)

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const columns = useMemo(
    () =>
      createLoggedInUsersColumns({
        pluginUrl,
        trackLoggedInEnabled: true, // Always true for logged-in users page
        storeIpEnabled: wp.isStoreIpEnabled(),
      }),
    [pluginUrl, wp]
  )

  // Hardcoded filter to show only logged-in users (excludes user #0)
  const loggedInFilter = useMemo(
    () => ({
      id: 'logged_in-hardcoded',
      label: __('User Type', 'wp-statistics'),
      operator: __('is', 'wp-statistics'),
      rawOperator: 'is',
      value: __('Logged-in', 'wp-statistics'),
      rawValue: '1',
    }),
    []
  )

  // Locked filter for display in Options drawer (read-only)
  const lockedFilters: LockedFilter[] = useMemo(
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

  // Merge hardcoded filter with user-applied filters
  const mergedFilters = useMemo(() => {
    return [loggedInFilter, ...(appliedFilters || [])]
  }, [loggedInFilter, appliedFilters])

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
      filters: mergedFilters,
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
    defaultColumnOrder,
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

  // Options drawer with column management - config is passed once and returned for drawer
  const options = useTableOptions({
    filterGroup: 'visitors',
    table: tableRef.current,
    lockedFilters,
    initialColumnOrder: defaultColumnOrder,
    columnOrder,
    defaultHiddenColumns: LOGGED_IN_USERS_DEFAULT_HIDDEN_COLUMNS,
    initialColumnVisibility,
    onColumnVisibilityChange: handleColumnVisibilityChange,
    onColumnOrderChange: handleColumnOrderChange,
    onReset: handleColumnPreferencesReset,
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

  const isChartLoading = isBatchFetching
  const showSkeleton = isBatchLoading && !batchResponse

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Logged-in Users', 'wp-statistics')}
        filterGroup="visitors"
        optionsTriggerProps={options.triggerProps}
        lockedFilters={lockedFilters}
      />

      {/* Options Drawer with Column Management */}
      <TableOptionsDrawer {...options} />

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
              tableRef={tableRef}
            />
          </>
        )}
      </div>
    </div>
  )
}
