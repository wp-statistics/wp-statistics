import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, Link, useNavigate } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { calcSharePercentage, formatCompactNumber, formatDecimal, formatDuration } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import {
  getIndividualContentQueryOptions,
  type TrafficSummaryPeriodResponse,
} from '@/services/content-analytics/get-individual-content'

// Helper to extract total value from API response (handles both flat and { current, previous } formats)
function getTotalValue(total: unknown): number {
  if (typeof total === 'number') return total
  if (typeof total === 'string') return Number(total) || 0
  if (typeof total === 'object' && total !== null) {
    const obj = total as { current?: number | string }
    return Number(obj.current) || 0
  }
  return 0
}

export const Route = createLazyFileRoute('/(content-analytics)/individual-content')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  const { resource_id } = Route.useSearch()

  // If no resource_id, show error
  if (!resource_id) {
    return (
      <div className="min-w-0 p-6">
        <div className="text-center">
          <h1 className="text-2xl font-semibold text-neutral-800 mb-2">
            {__('Content Not Found', 'wp-statistics')}
          </h1>
          <p className="text-muted-foreground mb-4">
            {__('No content ID was provided.', 'wp-statistics')}
          </p>
          <Link to="/content" className="text-primary hover:underline">
            {__('Go to Content Overview', 'wp-statistics')}
          </Link>
        </div>
      </div>
    )
  }

  return <IndividualContentView resourceId={resource_id} />
}

// Traffic summary row type
type TrafficSummaryPeriod = 'today' | 'yesterday' | 'last7days' | 'last28days'
interface TrafficSummaryRowData {
  period: TrafficSummaryPeriod
  visitors: number
  visitorsPrev: number
  views: number
  viewsPrev: number
}

// Traffic Summary Table Props
interface TrafficSummaryTableProps {
  data: {
    today?: TrafficSummaryPeriodResponse
    yesterday?: TrafficSummaryPeriodResponse
    last7days?: TrafficSummaryPeriodResponse
    last28days?: TrafficSummaryPeriodResponse
  }
}

/**
 * Traffic Summary Table - Shows traffic data across different time periods
 * Displays: Today, Yesterday, Last 7 Days, Last 28 Days
 * Per doc: Column Management Mode: "none" (static display), No pagination, No sorting
 */
function TrafficSummaryTable({ data }: TrafficSummaryTableProps) {
  const calcPercentage = usePercentageCalc()

  const periodLabels: Record<TrafficSummaryPeriod, string> = {
    today: __('Today', 'wp-statistics'),
    yesterday: __('Yesterday', 'wp-statistics'),
    last7days: __('Last 7 Days', 'wp-statistics'),
    last28days: __('Last 28 Days', 'wp-statistics'),
  }

  // Transform data from batch response into table rows
  const summaryData = useMemo((): TrafficSummaryRowData[] => {
    const periods: TrafficSummaryPeriod[] = ['today', 'yesterday', 'last7days', 'last28days']

    return periods.map((period) => {
      const periodData = data[period]
      const totals = periodData?.totals

      return {
        period,
        visitors: Number(totals?.visitors?.current) || 0,
        visitorsPrev: Number(totals?.visitors?.previous) || 0,
        views: Number(totals?.views?.current) || 0,
        viewsPrev: Number(totals?.views?.previous) || 0,
      }
    })
  }, [data])

  // Define columns for DataTable
  const columns = useMemo<ColumnDef<TrafficSummaryRowData>[]>(
    () => [
      {
        accessorKey: 'period',
        header: __('Time Period', 'wp-statistics'),
        enableSorting: false,
        cell: ({ row }) => (
          <span className="font-medium">{periodLabels[row.original.period]}</span>
        ),
      },
      {
        accessorKey: 'visitors',
        header: () => <div className="text-right">{__('Visitors', 'wp-statistics')}</div>,
        enableSorting: false,
        cell: ({ row }) => {
          const { visitors, visitorsPrev } = row.original
          const change = calcPercentage(visitors, visitorsPrev)

          return (
            <div className="text-right">
              <span>{visitors.toLocaleString()}</span>
              {change.percentage !== '0%' && (
                <span className={`ml-1.5 text-xs ${change.isNegative ? 'text-red-600' : 'text-green-600'}`}>
                  {change.isNegative ? '↓' : '↑'} {change.percentage}
                </span>
              )}
            </div>
          )
        },
      },
      {
        accessorKey: 'views',
        header: () => <div className="text-right">{__('Views', 'wp-statistics')}</div>,
        enableSorting: false,
        cell: ({ row }) => {
          const { views, viewsPrev } = row.original
          const change = calcPercentage(views, viewsPrev)

          return (
            <div className="text-right">
              <span>{views.toLocaleString()}</span>
              {change.percentage !== '0%' && (
                <span className={`ml-1.5 text-xs ${change.isNegative ? 'text-red-600' : 'text-green-600'}`}>
                  {change.isNegative ? '↓' : '↑'} {change.percentage}
                </span>
              )}
            </div>
          )
        },
      },
    ],
    [calcPercentage, periodLabels]
  )

  return (
    <DataTable
      columns={columns}
      data={summaryData}
      title={__('Traffic Summary', 'wp-statistics')}
      showColumnManagement={false}
      showPagination={false}
      mobileCardEnabled={false}
      emptyStateMessage={__('No traffic data available', 'wp-statistics')}
    />
  )
}

/**
 * Individual Content View - Detailed view for an individual content item
 */
function IndividualContentView({ resourceId }: { resourceId: number }) {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    filters: appliedFilters,
    setDateRange,
    applyFilters: handleApplyFilters,
    removeFilter: handleRemoveFilter,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const navigate = useNavigate()

  // Get filter fields for individual content
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('individual-content') as FilterField[]
  }, [wp])

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Track if only timeframe changed
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  useEffect(() => {
    const dateRangeChanged = dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current

    if (dateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [dateFrom, dateTo])

  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  // Get valid filter names for this page (based on filter groups)
  const validFilterNames = useMemo(() => {
    return new Set(filterFields.map((f) => f.name))
  }, [filterFields])

  // Filter applied filters to only include those valid for this page
  // This prevents filters from other pages from being applied here
  // Also filter out post_type since we're filtering by specific resource_id
  const filtersWithoutPostType = useMemo(() => {
    return (appliedFilters || []).filter((f) => {
      // Extract filter name from filter id (e.g., "author-author-filter-123" -> "author")
      const filterName = f.id.split('-')[0]
      // Must be valid for this page AND not be post_type
      return validFilterNames.has(filterName) && !f.id.startsWith('post_type')
    })
  }, [appliedFilters, validFilterNames])

  // Batch query for all individual content data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getIndividualContentQueryOptions({
      resourceId,
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
      filters: filtersWithoutPostType,
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const showSkeleton = isLoading && !batchResponse
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Extract data from batch response
  const contentMetadata = batchResponse?.data?.items?.content_metadata?.data?.rows?.[0]
  const metricsResponse = batchResponse?.data?.items?.content_metrics
  const trafficTrendsResponse = batchResponse?.data?.items?.traffic_trends
  const topReferrersData = batchResponse?.data?.items?.top_referrers?.data?.rows || []
  const topReferrersTotals = batchResponse?.data?.items?.top_referrers?.data?.totals
  const topSearchEnginesData = batchResponse?.data?.items?.top_search_engines?.data?.rows || []
  const topSearchEnginesTotals = batchResponse?.data?.items?.top_search_engines?.data?.totals
  const topCountriesData = batchResponse?.data?.items?.top_countries?.data?.rows || []
  const topCountriesTotals = batchResponse?.data?.items?.top_countries?.data?.totals
  const topBrowsersData = batchResponse?.data?.items?.top_browsers?.data?.rows || []
  const topBrowsersTotals = batchResponse?.data?.items?.top_browsers?.data?.totals
  const topOSData = batchResponse?.data?.items?.top_os?.data?.rows || []
  const topOSTotals = batchResponse?.data?.items?.top_os?.data?.totals
  const topDevicesData = batchResponse?.data?.items?.top_devices?.data?.rows || []
  const topDevicesTotals = batchResponse?.data?.items?.top_devices?.data?.totals

  // Extract traffic summary data from batch response
  const trafficSummaryData = useMemo(
    () => ({
      today: batchResponse?.data?.items?.traffic_summary_today,
      yesterday: batchResponse?.data?.items?.traffic_summary_yesterday,
      last7days: batchResponse?.data?.items?.traffic_summary_last7days,
      last28days: batchResponse?.data?.items?.traffic_summary_last28days,
      total: batchResponse?.data?.items?.traffic_summary_total,
    }),
    [batchResponse]
  )

  // Transform chart data
  const chartData = useMemo(() => {
    if (!trafficTrendsResponse?.labels || !trafficTrendsResponse?.datasets) return []

    const labels = trafficTrendsResponse.labels
    const datasets = trafficTrendsResponse.datasets
    const currentDatasets = datasets.filter((d) => !d.comparison)
    const previousDatasets = datasets.filter((d) => d.comparison)

    return labels.map((label, index) => {
      const point: { date: string; [key: string]: string | number } = { date: label }

      currentDatasets.forEach((dataset) => {
        point[dataset.key] = Number(dataset.data[index]) || 0
      })

      previousDatasets.forEach((dataset) => {
        const baseKey = dataset.key.replace('_previous', '')
        point[`${baseKey}Previous`] = Number(dataset.data[index]) || 0
      })

      return point
    })
  }, [trafficTrendsResponse])

  // Calculate chart totals
  const chartTotals = useMemo(() => {
    if (!trafficTrendsResponse?.datasets) {
      return { visitors: 0, visitorsPrevious: 0, views: 0, viewsPrevious: 0 }
    }

    const datasets = trafficTrendsResponse.datasets
    const visitorsDataset = datasets.find((d) => d.key === 'visitors' && !d.comparison)
    const visitorsPrevDataset = datasets.find((d) => d.key === 'visitors_previous' && d.comparison)
    const viewsDataset = datasets.find((d) => d.key === 'views' && !d.comparison)
    const viewsPrevDataset = datasets.find((d) => d.key === 'views_previous' && d.comparison)

    return {
      visitors: visitorsDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      visitorsPrevious: visitorsPrevDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      views: viewsDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      viewsPrevious: viewsPrevDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
    }
  }, [trafficTrendsResponse])

  const chartMetrics = [
    {
      key: 'visitors',
      label: __('Visitors', 'wp-statistics'),
      color: 'var(--chart-1)',
      enabled: true,
      value:
        chartTotals.visitors >= 1000
          ? `${formatDecimal(chartTotals.visitors / 1000)}k`
          : formatDecimal(chartTotals.visitors),
      previousValue:
        chartTotals.visitorsPrevious >= 1000
          ? `${formatDecimal(chartTotals.visitorsPrevious / 1000)}k`
          : formatDecimal(chartTotals.visitorsPrevious),
    },
    {
      key: 'views',
      label: __('Views', 'wp-statistics'),
      color: 'var(--chart-2)',
      enabled: true,
      value:
        chartTotals.views >= 1000 ? `${formatDecimal(chartTotals.views / 1000)}k` : formatDecimal(chartTotals.views),
      previousValue:
        chartTotals.viewsPrevious >= 1000
          ? `${formatDecimal(chartTotals.viewsPrevious / 1000)}k`
          : formatDecimal(chartTotals.viewsPrevious),
    },
  ]

  const calcPercentage = usePercentageCalc()

  // Build metrics - 8 metrics per documentation
  const contentMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const visitors = Number(totals.visitors?.current) || 0
    const views = Number(totals.views?.current) || 0
    const avgTimeOnPage = Number(totals.avg_time_on_page?.current) || 0
    const bounceRate = Number(totals.bounce_rate?.current) || 0
    const entryPage = Number(totals.entry_page?.current) || 0
    const exitPage = Number(totals.exit_page?.current) || 0
    const exitRate = Number(totals.exit_rate?.current) || 0
    const comments = Number(totals.comments?.current) || 0

    const prevVisitors = Number(totals.visitors?.previous) || 0
    const prevViews = Number(totals.views?.previous) || 0
    const prevAvgTimeOnPage = Number(totals.avg_time_on_page?.previous) || 0
    const prevBounceRate = Number(totals.bounce_rate?.previous) || 0
    const prevEntryPage = Number(totals.entry_page?.previous) || 0
    const prevExitPage = Number(totals.exit_page?.previous) || 0
    const prevExitRate = Number(totals.exit_rate?.previous) || 0
    const prevComments = Number(totals.comments?.previous) || 0

    const metrics: MetricItem[] = [
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...calcPercentage(visitors, prevVisitors),
        tooltipContent: __('Unique visitors to this content', 'wp-statistics'),
      },
      {
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...calcPercentage(views, prevViews),
        tooltipContent: __('Total page views', 'wp-statistics'),
      },
      {
        label: __('Avg. Time on Page', 'wp-statistics'),
        value: formatDuration(avgTimeOnPage),
        ...calcPercentage(avgTimeOnPage, prevAvgTimeOnPage),
        tooltipContent: __('Average time spent on this content', 'wp-statistics'),
      },
      {
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(bounceRate)}%`,
        ...calcPercentage(bounceRate, prevBounceRate),
        tooltipContent: __('Percentage of single-page sessions', 'wp-statistics'),
      },
      {
        label: __('Entry Page', 'wp-statistics'),
        value: formatCompactNumber(entryPage),
        ...calcPercentage(entryPage, prevEntryPage),
        tooltipContent: __('Times this page was the first page visited', 'wp-statistics'),
      },
      {
        label: __('Exit Page', 'wp-statistics'),
        value: formatCompactNumber(exitPage),
        ...calcPercentage(exitPage, prevExitPage),
        tooltipContent: __('Times this page was the last page visited', 'wp-statistics'),
      },
      {
        label: __('Exit Rate', 'wp-statistics'),
        value: `${formatDecimal(exitRate)}%`,
        ...calcPercentage(exitRate, prevExitRate),
        tooltipContent: __('Percentage of views that were the last page visited', 'wp-statistics'),
      },
      {
        label: __('Comments', 'wp-statistics'),
        value: formatCompactNumber(comments),
        ...calcPercentage(comments, prevComments),
        tooltipContent: __('Total comments on this content', 'wp-statistics'),
      },
    ]

    return metrics
  }, [metricsResponse, calcPercentage])

  // Format date for display
  const formatDate = (dateStr: string | null | undefined): string => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
  }

  // Check if modified date is different from published date
  const showModifiedDate = useMemo(() => {
    if (!contentMetadata?.published_date || !contentMetadata?.modified_date) return false
    const published = contentMetadata.published_date.split(' ')[0] // Get date part only
    const modified = contentMetadata.modified_date.split(' ')[0]
    return published !== modified
  }, [contentMetadata])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="px-4 py-3 bg-white border-b border-input">
        {/* Title row with filter and date picker */}
        <div className="flex items-center justify-between gap-4 mb-2">
          {/* Content Title with View/Edit icons */}
          <div className="flex items-center gap-2 min-w-0 flex-1">
            <h1 className="text-lg font-semibold text-neutral-800 truncate">
              {contentMetadata?.page_title || __('Untitled', 'wp-statistics')}
            </h1>
            {contentMetadata?.permalink && (
              <a
                href={contentMetadata.permalink}
                target="_blank"
                rel="noopener noreferrer"
                className="text-muted-foreground hover:text-foreground flex-shrink-0"
                title={__('View content', 'wp-statistics')}
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
              </a>
            )}
            {contentMetadata?.edit_url && (
              <a
                href={contentMetadata.edit_url}
                target="_blank"
                rel="noopener noreferrer"
                className="text-muted-foreground hover:text-foreground flex-shrink-0"
                title={__('Edit content', 'wp-statistics')}
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </a>
            )}
          </div>
          <div className="flex items-center gap-3 flex-shrink-0">
            {filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={filtersWithoutPostType}
                onApplyFilters={handleApplyFilters}
              />
            )}
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
        </div>

        {/* Content Meta */}
        {contentMetadata && (
          <>
            {/* Content Meta (Line 1) */}
            <div className="flex items-center gap-3 text-sm text-muted-foreground">
              {/* Post Type */}
              {contentMetadata.post_type_label && (
                <Link
                  to="/content"
                  search={{ post_type: contentMetadata.page_type }}
                  className="hover:text-foreground hover:underline"
                >
                  {contentMetadata.post_type_label}
                </Link>
              )}

              {/* Published Date */}
              {contentMetadata.published_date && (
                <>
                  <span className="text-muted-foreground/50">•</span>
                  <span>{__('Published:', 'wp-statistics')} {formatDate(contentMetadata.published_date)}</span>
                </>
              )}

              {/* Last Updated (only if different from published) */}
              {showModifiedDate && (
                <>
                  <span className="text-muted-foreground/50">•</span>
                  <span>{__('Updated:', 'wp-statistics')} {formatDate(contentMetadata.modified_date)}</span>
                </>
              )}

              {/* Author */}
              {contentMetadata.author_name && contentMetadata.author_id && (
                <>
                  <span className="text-muted-foreground/50">•</span>
                  <Link
                    to="/individual-author"
                    search={{ author_id: contentMetadata.author_id }}
                    className="hover:text-foreground hover:underline"
                  >
                    {contentMetadata.author_name}
                  </Link>
                </>
              )}
            </div>

            {/* Content Terms (Line 2) */}
            {contentMetadata.cached_terms && contentMetadata.cached_terms.length > 0 && (
              <div className="flex items-center gap-2 mt-1.5 text-sm text-muted-foreground flex-wrap">
                {contentMetadata.cached_terms.map((term) => (
                  <Link
                    key={term.term_id}
                    to="/categories"
                    search={{ term: term.term_id, taxonomy: term.taxonomy }}
                    className="inline-flex items-center px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-700 hover:bg-neutral-200 hover:text-neutral-900 transition-colors text-xs"
                  >
                    {term.name}
                  </Link>
                ))}
              </div>
            )}
          </>
        )}
      </div>

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="individual-content" />
        {filtersWithoutPostType.length > 0 && (
          <FilterBar
            filters={filtersWithoutPostType}
            onRemoveFilter={handleRemoveFilter}
            className="mb-2"
          />
        )}

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={8} columns={4} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-4">
              <PanelSkeleton>
                <div className="space-y-2">
                  {[1, 2, 3, 4, 5].map((i) => (
                    <div key={i} className="h-8 bg-neutral-100 rounded animate-pulse" />
                  ))}
                </div>
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-8">
              <PanelSkeleton titleWidth="w-32">
                <ChartSkeleton height={256} showTitle={false} />
              </PanelSkeleton>
            </div>
            {[1, 2, 3].map((i) => (
              <div key={i} className="col-span-12 lg:col-span-4">
                <PanelSkeleton>
                  <BarListSkeleton items={5} />
                </PanelSkeleton>
              </div>
            ))}
            {[1, 2, 3].map((i) => (
              <div key={`device-${i}`} className="col-span-12 lg:col-span-4">
                <PanelSkeleton>
                  <BarListSkeleton items={5} showIcon />
                </PanelSkeleton>
              </div>
            ))}
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Individual Content Metrics */}
            <div className="col-span-12">
              <Panel>
                <Metrics metrics={contentMetrics} columns={4} />
              </Panel>
            </div>

            {/* Row 2: Traffic Summary (1/3) + Traffic Trends (2/3) */}
            <div className="col-span-12 lg:col-span-4">
              <TrafficSummaryTable data={trafficSummaryData} />
            </div>
            <div className="col-span-12 lg:col-span-8">
              <LineChart
                title={__('Traffic Trends', 'wp-statistics')}
                data={chartData}
                metrics={chartMetrics}
                showPreviousPeriod={true}
                timeframe={timeframe}
                onTimeframeChange={handleTimeframeChange}
                loading={isChartRefetching}
              />
            </div>

            {/* Row 3: Traffic Sources (3 columns) - With links to full reports */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Referrers', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topReferrersTotals?.visitors) || 1
                  return topReferrersData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const displayName = item.referrer_name || item.referrer_domain || __('Direct', 'wp-statistics')

                    return {
                      label: displayName,
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: displayName,
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View All Referrers', 'wp-statistics'),
                  action: () => navigate({ to: '/referrers' }),
                }}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Search Engines', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topSearchEnginesTotals?.visitors) || 1
                  return topSearchEnginesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const displayName = item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics')

                    return {
                      label: displayName,
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: displayName,
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View All Search Engines', 'wp-statistics'),
                  action: () => navigate({ to: '/search-engines' }),
                }}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Countries', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topCountriesTotals?.visitors) || 1
                  return topCountriesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)

                    return {
                      icon: (
                        <img
                          src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
                          alt={item.country_name || ''}
                          className="w-4 h-3"
                        />
                      ),
                      label: item.country_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.country_name || '',
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View All Countries', 'wp-statistics'),
                  action: () => navigate({ to: '/geographic' }),
                }}
              />
            </div>

            {/* Row 4: Device Analytics (3 columns) - No "See all" links per spec */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Browsers', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topBrowsersTotals?.visitors) || 1
                  return topBrowsersData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const iconName = (item.browser_name || 'unknown').toLowerCase()

                    return {
                      icon: (
                        <img
                          src={`${pluginUrl}public/images/browser/${iconName}.svg`}
                          alt={item.browser_name || ''}
                          className="w-4 h-3"
                        />
                      ),
                      label: item.browser_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.browser_name || '',
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Operating Systems', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topOSTotals?.visitors) || 1
                  return topOSData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const iconName = (item.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_')

                    return {
                      icon: (
                        <img
                          src={`${pluginUrl}public/images/operating-system/${iconName}.svg`}
                          alt={item.os_name || ''}
                          className="w-4 h-3"
                        />
                      ),
                      label: item.os_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.os_name || '',
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Device Categories', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = getTotalValue(topDevicesTotals?.visitors) || 1
                  return topDevicesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const iconName = (item.device_type_name || 'desktop').toLowerCase()

                    return {
                      icon: (
                        <img
                          src={`${pluginUrl}public/images/device/${iconName}.svg`}
                          alt={item.device_type_name || ''}
                          className="w-4 h-3"
                        />
                      ),
                      label: item.device_type_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.device_type_name || '',
                      tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
              />
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
