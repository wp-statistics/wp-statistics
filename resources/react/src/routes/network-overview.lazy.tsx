import { useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { Metrics } from '@/components/custom/metrics'
import { createNetworkSitesColumns } from '@/components/data-table-columns/network-sites-columns'
import { Panel } from '@/components/ui/panel'
import { Skeleton } from '@/components/ui/skeleton'
import { MetricsSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { formatCompactNumber } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getNetworkStatsQueryOptions } from '@/services/network/get-network-stats'

export const Route = createLazyFileRoute('/network-overview')({
  component: NetworkOverviewComponent,
})

function NetworkOverviewComponent() {
  const wp = WordPress.getInstance()
  const isNetworkAdmin = wp.isNetworkAdmin()

  // Use global filters context for date range (syncs with URL)
  const { dateFrom, dateTo, compareDateFrom, compareDateTo, period, handleDateRangeUpdate, isInitialized, apiDateParams, isCompareEnabled } =
    useGlobalFilters()

  // Use percentage calculation hook
  const calcPercentage = usePercentageCalc()

  // Fetch network stats
  const {
    data: response,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getNetworkStatsQueryOptions({
      date_from: apiDateParams.date_from,
      date_to: apiDateParams.date_to,
      previous_date_from: apiDateParams.previous_date_from,
      previous_date_to: apiDateParams.previous_date_to,
    }),
    enabled: isNetworkAdmin && isInitialized && !!apiDateParams.date_from && !!apiDateParams.date_to,
  })

  const networkData = response?.data

  // Sites data
  const sitesData = useMemo(() => {
    return networkData?.sites ?? []
  }, [networkData])

  // Create columns using shared factory
  const columns = useMemo(() => createNetworkSitesColumns(), [])

  // Metrics data for summary
  const summaryMetrics = useMemo(() => {
    const visitors = networkData?.totals?.visitors ?? 0
    const views = networkData?.totals?.views ?? 0
    const sessions = networkData?.totals?.sessions ?? 0
    const prevVisitors = networkData?.previous_totals?.visitors ?? 0
    const prevViews = networkData?.previous_totals?.views ?? 0
    const prevSessions = networkData?.previous_totals?.sessions ?? 0

    return [
      {
        label: __('Total Sites', 'wp-statistics'),
        value: formatCompactNumber(sitesData.length),
        tooltipContent: __('Sites in your network.', 'wp-statistics'),
      },
      {
        label: __('Total Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        tooltipContent: __('Unique visitors across all sites.', 'wp-statistics'),
        ...(isCompareEnabled ? calcPercentage(visitors, prevVisitors) : {}),
      },
      {
        label: __('Total Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        tooltipContent: __('Page views across all sites.', 'wp-statistics'),
        ...(isCompareEnabled ? calcPercentage(views, prevViews) : {}),
      },
      {
        label: __('Total Sessions', 'wp-statistics'),
        value: formatCompactNumber(sessions),
        tooltipContent: __('Sessions across all sites.', 'wp-statistics'),
        ...(isCompareEnabled ? calcPercentage(sessions, prevSessions) : {}),
      },
    ]
  }, [sitesData.length, networkData?.totals, networkData?.previous_totals, calcPercentage, isCompareEnabled])

  // Check if we're in network admin context (after hooks)
  if (!isNetworkAdmin) {
    return (
      <div className="p-6">
        <ErrorMessage message={__('This page is only available in Network Admin.', 'wp-statistics')} />
      </div>
    )
  }

  // Show loading state while initializing
  if (!isInitialized) {
    return (
      <div className="min-w-0">
        <div className="flex items-center justify-between px-4 py-3 ">
          <Skeleton className="h-7 w-40" />
          <Skeleton className="h-9 w-64" />
        </div>
        <div className="p-4 space-y-4">
          <Panel>
            <MetricsSkeleton count={4} columns={4} />
          </Panel>
          <TableSkeleton rows={5} />
        </div>
      </div>
    )
  }

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Network Overview', 'wp-statistics')}</h1>
        <DateRangePicker
          initialDateFrom={dateFrom}
          initialDateTo={dateTo}
          initialCompareFrom={compareDateFrom}
          initialCompareTo={compareDateTo}
          initialPeriod={period}
          showCompare={true}
          onUpdate={handleDateRangeUpdate}
          align="end"
        />
      </div>

      <div className="p-4 space-y-4">
        {isError ? (
          <ErrorMessage message={error?.message || __('Failed to load network statistics', 'wp-statistics')} />
        ) : (
          <>
            {/* Summary Metrics */}
            <Panel>
              {isFetching ? (
                <MetricsSkeleton count={4} columns={4} />
              ) : (
                <Metrics metrics={summaryMetrics} columns={4} />
              )}
            </Panel>

            {/* Sites Table */}
            <DataTable
              columns={columns}
              data={sitesData}
              title={__('Sites Statistics', 'wp-statistics')}
              showPagination={sitesData.length > 10}
              rowLimit={10}
              isFetching={isFetching}
              emptyStateMessage={__('No sites found', 'wp-statistics')}
            />
          </>
        )}
      </div>
    </div>
  )
}
