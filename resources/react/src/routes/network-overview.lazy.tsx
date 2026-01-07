import type { ColumnDef } from '@tanstack/react-table'
import { useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { ExternalLink } from 'lucide-react'
import { useCallback, useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { Metrics } from '@/components/custom/metrics'
import { Button } from '@/components/ui/button'
import { Panel } from '@/components/ui/panel'
import { Skeleton } from '@/components/ui/skeleton'
import { MetricsSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { formatCompactNumber } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getNetworkStatsQueryOptions, type NetworkSiteStats } from '@/services/network/get-network-stats'

export const Route = createLazyFileRoute('/network-overview')({
  component: NetworkOverviewComponent,
})

// Column definitions for the sites table
const createSitesColumns = (): ColumnDef<NetworkSiteStats>[] => [
  {
    accessorKey: 'name',
    header: __('Site', 'wp-statistics'),
    cell: ({ row }) => {
      const site = row.original
      return (
        <div>
          <div className="font-medium">{site.name}</div>
          <a
            href={site.url}
            target="_blank"
            rel="noopener noreferrer"
            className="text-sm text-muted-foreground hover:text-primary hover:underline"
          >
            {site.url}
          </a>
        </div>
      )
    },
    enableHiding: false,
  },
  {
    accessorKey: 'visitors',
    header: () => <div className="text-center w-full">{__('Visitors', 'wp-statistics')}</div>,
    cell: ({ row }) => {
      const site = row.original
      if (site.error) {
        return <div className="text-center text-destructive text-sm">{__('Error', 'wp-statistics')}</div>
      }
      return <div className="text-center">{formatCompactNumber(site.visitors)}</div>
    },
  },
  {
    accessorKey: 'views',
    header: () => <div className="text-center w-full">{__('Views', 'wp-statistics')}</div>,
    cell: ({ row }) => {
      const site = row.original
      if (site.error) return <div className="text-center">-</div>
      return <div className="text-center">{formatCompactNumber(site.views)}</div>
    },
  },
  {
    accessorKey: 'sessions',
    header: () => <div className="text-center w-full">{__('Sessions', 'wp-statistics')}</div>,
    cell: ({ row }) => {
      const site = row.original
      if (site.error) return <div className="text-center">-</div>
      return <div className="text-center">{formatCompactNumber(site.sessions)}</div>
    },
  },
  {
    id: 'actions',
    header: () => <div className="text-right w-full">{__('Actions', 'wp-statistics')}</div>,
    cell: ({ row }) => {
      const site = row.original
      return (
        <div className="text-right">
          <Button variant="outline" size="sm" asChild>
            <a href={site.admin_url} target="_blank" rel="noopener noreferrer">
              <ExternalLink className="h-3.5 w-3.5 mr-1.5" />
              {__('Dashboard', 'wp-statistics')}
            </a>
          </Button>
        </div>
      )
    },
    enableHiding: false,
    enableSorting: false,
  },
]

function NetworkOverviewComponent() {
  const wp = WordPress.getInstance()

  // Check if we're in network admin context
  if (!wp.isNetworkAdmin()) {
    return (
      <div className="p-6">
        <ErrorMessage message={__('This page is only available in Network Admin.', 'wp-statistics')} />
      </div>
    )
  }

  // Use global filters context for date range (syncs with URL)
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    setDateRange,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  // Use percentage calculation hook
  const calcPercentage = usePercentageCalc()

  // Handle date range updates
  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange }) => {
      setDateRange(values.range, values.rangeCompare)
    },
    [setDateRange]
  )

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
    enabled: isInitialized && !!apiDateParams.date_from && !!apiDateParams.date_to,
  })

  const networkData = response?.data

  // Sites data
  const sitesData = useMemo(() => {
    return networkData?.sites ?? []
  }, [networkData])

  // Create columns
  const columns = useMemo(() => createSitesColumns(), [])

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
        ...calcPercentage(visitors, prevVisitors),
      },
      {
        label: __('Total Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        tooltipContent: __('Page views across all sites.', 'wp-statistics'),
        ...calcPercentage(views, prevViews),
      },
      {
        label: __('Total Sessions', 'wp-statistics'),
        value: formatCompactNumber(sessions),
        tooltipContent: __('Sessions across all sites.', 'wp-statistics'),
        ...calcPercentage(sessions, prevSessions),
      },
    ]
  }, [sitesData.length, networkData?.totals, networkData?.previous_totals, calcPercentage])

  // Show loading state while initializing
  if (!isInitialized) {
    return (
      <div className="min-w-0">
        <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
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
      <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
        <h1 className="text-xl font-semibold text-neutral-800">
          {__('Network Overview', 'wp-statistics')}
        </h1>
        <DateRangePicker
          initialDateFrom={dateFrom}
          initialDateTo={dateTo}
          initialCompareFrom={compareDateFrom}
          initialCompareTo={compareDateTo}
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
              showColumnManagement={false}
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
