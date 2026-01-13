import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, Link } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import { TabbedList, type TabbedListTab } from '@/components/custom/tabbed-list'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { calcSharePercentage, formatCompactNumber, formatDecimal, formatDuration } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import {
  getIndividualCategoryQueryOptions,
  type TrafficSummaryPeriodResponse,
} from '@/services/content-analytics/get-individual-category'

export const Route = createLazyFileRoute('/(content-analytics)/individual-category')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  const { term_id } = Route.useSearch()

  // If no term_id, show error
  if (!term_id) {
    return (
      <div className="min-w-0 p-6">
        <div className="text-center">
          <h1 className="text-2xl font-semibold text-neutral-800 mb-2">{__('Category Not Found', 'wp-statistics')}</h1>
          <p className="text-muted-foreground mb-4">{__('No term ID was provided.', 'wp-statistics')}</p>
          <Link to="/categories" className="text-primary hover:underline">
            {__('Go to Categories Overview', 'wp-statistics')}
          </Link>
        </div>
      </div>
    )
  }

  return <IndividualCategoryView termId={term_id} />
}

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

// Traffic summary row type
type TrafficSummaryPeriod = 'today' | 'yesterday' | 'last7days' | 'last28days' | 'total'
interface TrafficSummaryRowData {
  period: TrafficSummaryPeriod
  visitors: number
  visitorsPrev: number | null // null for Total row (no comparison)
  views: number
  viewsPrev: number | null // null for Total row (no comparison)
}

// Traffic Summary Table Props
interface TrafficSummaryTableProps {
  data: {
    today?: TrafficSummaryPeriodResponse
    yesterday?: TrafficSummaryPeriodResponse
    last7days?: TrafficSummaryPeriodResponse
    last28days?: TrafficSummaryPeriodResponse
    total?: TrafficSummaryPeriodResponse
  }
}

/**
 * Traffic Summary Table - Shows traffic data across different time periods
 */
function TrafficSummaryTable({ data }: TrafficSummaryTableProps) {
  const calcPercentage = usePercentageCalc()

  // Transform data from batch response into table rows
  const summaryData = useMemo((): TrafficSummaryRowData[] => {
    const periodsWithComparison: Exclude<TrafficSummaryPeriod, 'total'>[] = [
      'today',
      'yesterday',
      'last7days',
      'last28days',
    ]

    const rows: TrafficSummaryRowData[] = periodsWithComparison.map((period) => {
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

    // Add Total row (no comparison)
    if (data.total) {
      const totalTotals = data.total?.totals
      rows.push({
        period: 'total',
        visitors: Number(totalTotals?.visitors?.current) || 0,
        visitorsPrev: null,
        views: Number(totalTotals?.views?.current) || 0,
        viewsPrev: null,
      })
    }

    return rows
  }, [data])

  // Define columns for DataTable
  const columns = useMemo<ColumnDef<TrafficSummaryRowData>[]>(() => {
    const periodLabels: Record<TrafficSummaryPeriod, string> = {
      today: __('Today', 'wp-statistics'),
      yesterday: __('Yesterday', 'wp-statistics'),
      last7days: __('Last 7 Days', 'wp-statistics'),
      last28days: __('Last 28 Days', 'wp-statistics'),
      total: __('Total', 'wp-statistics'),
    }

    return [
      {
        accessorKey: 'period',
        header: __('Time Period', 'wp-statistics'),
        enableSorting: false,
        cell: ({ row }) => <span className="font-medium">{periodLabels[row.original.period]}</span>,
      },
      {
        accessorKey: 'visitors',
        header: () => <div className="text-right">{__('Visitors', 'wp-statistics')}</div>,
        enableSorting: false,
        cell: ({ row }) => {
          const { visitors, visitorsPrev } = row.original

          // No comparison for Total row
          if (visitorsPrev === null) {
            return <div className="text-right">{visitors.toLocaleString()}</div>
          }

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

          // No comparison for Total row
          if (viewsPrev === null) {
            return <div className="text-right">{views.toLocaleString()}</div>
          }

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
    ]
  }, [calcPercentage])

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
 * Individual Category View - Detailed view for a single category/tag/taxonomy term
 */
function IndividualCategoryView({ termId }: { termId: number }) {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    setDateRange,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')
  const [activeContentTab, setActiveContentTab] = useState<string>('popular')
  const [activeAuthorsTab, setActiveAuthorsTab] = useState<string>('views')

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

  // Batch query for all individual category data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getIndividualCategoryQueryOptions({
      termId,
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const showSkeleton = isLoading && !batchResponse
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Extract data from batch response
  const termMetadata = batchResponse?.data?.items?.term_metadata?.data?.rows?.[0]
  const metricsResponse = batchResponse?.data?.items?.category_metrics
  const categoriesPerformanceResponse = batchResponse?.data?.items?.categories_performance
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
    if (!categoriesPerformanceResponse?.labels || !categoriesPerformanceResponse?.datasets) return []

    const labels = categoriesPerformanceResponse.labels
    const datasets = categoriesPerformanceResponse.datasets
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
  }, [categoriesPerformanceResponse])

  // Calculate chart totals
  const chartTotals = useMemo(() => {
    if (!categoriesPerformanceResponse?.datasets) {
      return {
        visitors: 0,
        visitorsPrevious: 0,
        views: 0,
        viewsPrevious: 0,
        publishedContent: 0,
        publishedContentPrevious: 0,
      }
    }

    const datasets = categoriesPerformanceResponse.datasets
    const visitorsDataset = datasets.find((d) => d.key === 'visitors' && !d.comparison)
    const visitorsPrevDataset = datasets.find((d) => d.key === 'visitors_previous' && d.comparison)
    const viewsDataset = datasets.find((d) => d.key === 'views' && !d.comparison)
    const viewsPrevDataset = datasets.find((d) => d.key === 'views_previous' && d.comparison)
    const publishedContentDataset = datasets.find((d) => d.key === 'published_content' && !d.comparison)
    const publishedContentPrevDataset = datasets.find((d) => d.key === 'published_content_previous' && d.comparison)

    return {
      visitors: visitorsDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      visitorsPrevious: visitorsPrevDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      views: viewsDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      viewsPrevious: viewsPrevDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      publishedContent: publishedContentDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
      publishedContentPrevious: publishedContentPrevDataset?.data?.reduce((sum: number, v) => sum + Number(v), 0) || 0,
    }
  }, [categoriesPerformanceResponse])

  const chartMetrics = useMemo(
    () => [
      {
        key: 'visitors',
        label: __('Visitors', 'wp-statistics'),
        color: 'var(--chart-1)',
        enabled: true,
        value:
          chartTotals.visitors >= 1000
            ? `${formatDecimal(chartTotals.visitors / 1000)}k`
            : formatDecimal(chartTotals.visitors),
        ...(isCompareEnabled
          ? {
              previousValue:
                chartTotals.visitorsPrevious >= 1000
                  ? `${formatDecimal(chartTotals.visitorsPrevious / 1000)}k`
                  : formatDecimal(chartTotals.visitorsPrevious),
            }
          : {}),
      },
      {
        key: 'views',
        label: __('Views', 'wp-statistics'),
        color: 'var(--chart-2)',
        enabled: true,
        value:
          chartTotals.views >= 1000 ? `${formatDecimal(chartTotals.views / 1000)}k` : formatDecimal(chartTotals.views),
        ...(isCompareEnabled
          ? {
              previousValue:
                chartTotals.viewsPrevious >= 1000
                  ? `${formatDecimal(chartTotals.viewsPrevious / 1000)}k`
                  : formatDecimal(chartTotals.viewsPrevious),
            }
          : {}),
      },
      {
        key: 'published_content',
        label: __('Published Content', 'wp-statistics'),
        color: 'var(--chart-3)',
        enabled: true,
        chartType: 'bar' as const,
        value:
          chartTotals.publishedContent >= 1000
            ? `${formatDecimal(chartTotals.publishedContent / 1000)}k`
            : formatDecimal(chartTotals.publishedContent),
        ...(isCompareEnabled
          ? {
              previousValue:
                chartTotals.publishedContentPrevious >= 1000
                  ? `${formatDecimal(chartTotals.publishedContentPrevious / 1000)}k`
                  : formatDecimal(chartTotals.publishedContentPrevious),
            }
          : {}),
      },
    ],
    [chartTotals, isCompareEnabled]
  )

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Build metrics - per documentation
  const categoryMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const publishedContent = Number(totals.published_content?.current) || 0
    const visitors = Number(totals.visitors?.current) || 0
    const views = Number(totals.views?.current) || 0
    const bounceRate = Number(totals.bounce_rate?.current) || 0
    const avgTimeOnPage = Number(totals.avg_time_on_page?.current) || 0
    const comments = Number(totals.comments?.current) || 0

    const prevPublishedContent = Number(totals.published_content?.previous) || 0
    const prevVisitors = Number(totals.visitors?.previous) || 0
    const prevViews = Number(totals.views?.previous) || 0
    const prevBounceRate = Number(totals.bounce_rate?.previous) || 0
    const prevAvgTimeOnPage = Number(totals.avg_time_on_page?.previous) || 0
    const prevComments = Number(totals.comments?.previous) || 0

    // Calculate avg comments per content
    const avgCommentsPerContent = publishedContent > 0 ? comments / publishedContent : 0
    const prevAvgCommentsPerContent = prevPublishedContent > 0 ? prevComments / prevPublishedContent : 0

    const metrics: MetricItem[] = [
      {
        label: __('Contents', 'wp-statistics'),
        value: formatCompactNumber(publishedContent),
        ...(isCompareEnabled ? calcPercentage(publishedContent, prevPublishedContent) : {}),
        tooltipContent: __('Number of content items in this term', 'wp-statistics'),
      },
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...(isCompareEnabled ? calcPercentage(visitors, prevVisitors) : {}),
        tooltipContent: __('Unique visitors to content in this term', 'wp-statistics'),
      },
      {
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...(isCompareEnabled ? calcPercentage(views, prevViews) : {}),
        tooltipContent: __('Total page views', 'wp-statistics'),
      },
      {
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(bounceRate)}%`,
        ...(isCompareEnabled ? calcPercentage(bounceRate, prevBounceRate) : {}),
        tooltipContent: __('Percentage of single-page sessions', 'wp-statistics'),
      },
      {
        label: __('Avg. Time on Page', 'wp-statistics'),
        value: formatDuration(avgTimeOnPage),
        ...(isCompareEnabled ? calcPercentage(avgTimeOnPage, prevAvgTimeOnPage) : {}),
        tooltipContent: __('Average time spent on pages', 'wp-statistics'),
      },
      {
        label: __('Comments', 'wp-statistics'),
        value: formatCompactNumber(comments),
        ...(isCompareEnabled ? calcPercentage(comments, prevComments) : {}),
        tooltipContent: __('Total comments on content in this term', 'wp-statistics'),
      },
      {
        label: __('Avg. Comments per Content', 'wp-statistics'),
        value: formatDecimal(avgCommentsPerContent),
        ...(isCompareEnabled ? calcPercentage(avgCommentsPerContent, prevAvgCommentsPerContent) : {}),
        tooltipContent: __('Average comments per content item', 'wp-statistics'),
      },
    ]

    return metrics
  }, [metricsResponse, calcPercentage, isCompareEnabled])

  // Build top content tabs
  const topContentTabs: TabbedListTab[] = useMemo(() => {
    const topContentPopular = batchResponse?.data?.items?.top_content_popular?.data?.rows || []
    const topContentRecent = batchResponse?.data?.items?.top_content_recent?.data?.rows || []
    const topContentCommented = batchResponse?.data?.items?.top_content_commented?.data?.rows || []

    const tabs: TabbedListTab[] = [
      {
        id: 'popular',
        label: __('Most Popular', 'wp-statistics'),
        items: topContentPopular.map((item) => ({
          id: String(item.resource_id),
          title: item.page_title || __('Unknown Page', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: item.thumbnail_url || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-content?resource_id=${item.resource_id}`,
        })),
      },
      {
        id: 'commented',
        label: __('Most Commented', 'wp-statistics'),
        items: topContentCommented.map((item) => ({
          id: String(item.resource_id),
          title: item.page_title || __('Unknown Page', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.comments))} ${__('comments', 'wp-statistics')} · ${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: item.thumbnail_url || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-content?resource_id=${item.resource_id}`,
        })),
      },
      {
        id: 'recent',
        label: __('Most Recent', 'wp-statistics'),
        items: topContentRecent.map((item) => ({
          id: String(item.resource_id),
          title: item.page_title || __('Unknown Page', 'wp-statistics'),
          subtitle: `${item.published_date || ''} · ${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: item.thumbnail_url || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-content?resource_id=${item.resource_id}`,
        })),
      },
    ]

    return tabs
  }, [batchResponse, pluginUrl])

  // Build top authors tabs
  const topAuthorsTabs: TabbedListTab[] = useMemo(() => {
    const topAuthorsViews = batchResponse?.data?.items?.top_authors_views?.data?.rows || []
    const topAuthorsPublishing = batchResponse?.data?.items?.top_authors_publishing?.data?.rows || []

    const tabs: TabbedListTab[] = [
      {
        id: 'views',
        label: __('By Views', 'wp-statistics'),
        items: topAuthorsViews.map((item) => ({
          id: String(item.author_id),
          title: item.author_name || __('Unknown Author', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')} · ${formatCompactNumber(Number(item.published_content))} ${__('contents', 'wp-statistics')}`,
          thumbnail: item.author_avatar || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-author?author_id=${item.author_id}`,
        })),
      },
      {
        id: 'publishing',
        label: __('By Publishing', 'wp-statistics'),
        items: topAuthorsPublishing.map((item) => ({
          id: String(item.author_id),
          title: item.author_name || __('Unknown Author', 'wp-statistics'),
          subtitle: `${formatCompactNumber(Number(item.published_content))} ${__('contents', 'wp-statistics')} · ${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`,
          thumbnail: item.author_avatar || `${pluginUrl}public/images/placeholder.png`,
          href: `/individual-author?author_id=${item.author_id}`,
        })),
      },
    ]

    return tabs
  }, [batchResponse, pluginUrl])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="px-4 py-3 ">
        {/* Title row with filter and date picker */}
        <div className="flex items-center justify-between gap-4 mb-2">
          {/* Term Name with action icons */}
          <div className="flex items-center gap-3 min-w-0 flex-1">
            <div className="min-w-0">
              <div className="flex items-center gap-2">
                <h1 className="text-lg font-semibold text-neutral-800 truncate">
                  {termMetadata?.term_name || __('Unknown Category', 'wp-statistics')}
                </h1>
                {termMetadata?.term_link && (
                  <a
                    href={termMetadata.term_link}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-muted-foreground hover:text-foreground flex-shrink-0"
                    title={__('View term archive', 'wp-statistics')}
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                      />
                    </svg>
                  </a>
                )}
              </div>
            </div>
          </div>
          <div className="flex items-center gap-3 flex-shrink-0">
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

        {/* Term Meta */}
        {termMetadata && (
          <div className="flex items-center gap-3 text-sm text-muted-foreground">
            {/* Taxonomy Type */}
            {termMetadata.taxonomy_label && <span className="capitalize">{termMetadata.taxonomy_label}</span>}
          </div>
        )}
      </div>

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="individual-category" />

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={7} columns={4} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-4">
              <PanelSkeleton>
                <div className="space-y-2">
                  {[1, 2, 3, 4].map((i) => (
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
            <div className="col-span-12">
              <PanelSkeleton>
                <BarListSkeleton items={5} showIcon />
              </PanelSkeleton>
            </div>
            <div className="col-span-12">
              <PanelSkeleton>
                <BarListSkeleton items={5} showIcon />
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
            {/* Row 1: Individual Category Metrics */}
            <div className="col-span-12">
              <Panel>
                <Metrics metrics={categoryMetrics} columns={4} />
              </Panel>
            </div>

            {/* Row 2: Traffic Summary (1/3) + Categories Performance (2/3) */}
            <div className="col-span-12 lg:col-span-4">
              <TrafficSummaryTable data={trafficSummaryData} />
            </div>
            <div className="col-span-12 lg:col-span-8">
              <LineChart
                title={__('Categories Performance', 'wp-statistics')}
                data={chartData}
                metrics={chartMetrics}
                showPreviousPeriod={isCompareEnabled}
                timeframe={timeframe}
                onTimeframeChange={handleTimeframeChange}
                loading={isChartRefetching}
                dateTo={apiDateParams.date_to}
              />
            </div>

            {/* Row 3: Top Content */}
            <div className="col-span-12">
              <TabbedList
                title={__('Top Content', 'wp-statistics')}
                tabs={topContentTabs}
                activeTab={activeContentTab}
                onTabChange={setActiveContentTab}
                emptyMessage={__('No content available for the selected period', 'wp-statistics')}
              />
            </div>

            {/* Row 4: Top Authors */}
            <div className="col-span-12">
              <TabbedList
                title={__('Top Authors', 'wp-statistics')}
                tabs={topAuthorsTabs}
                activeTab={activeAuthorsTab}
                onTabChange={setActiveAuthorsTab}
                emptyMessage={__('No authors available for the selected period', 'wp-statistics')}
              />
            </div>

            {/* Row 5: Traffic Sources (3 columns) */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Referrers', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={(() => {
                  const totalVisitors = getTotalValue(topReferrersTotals?.visitors) || 1
                  return topReferrersData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const displayName = item.referrer_name || item.referrer_domain || __('Direct', 'wp-statistics')
                    const comparisonProps = isCompareEnabled
                      ? {
                          ...calcPercentage(currentValue, previousValue),
                          tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                          comparisonDateLabel,
                        }
                      : {}

                    return {
                      label: displayName,
                      value: currentValue,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      tooltipTitle: displayName,
                      ...comparisonProps,
                    }
                  })
                })()}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Search Engines', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={(() => {
                  const totalVisitors = getTotalValue(topSearchEnginesTotals?.visitors) || 1
                  return topSearchEnginesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const displayName = item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics')
                    const comparisonProps = isCompareEnabled
                      ? {
                          ...calcPercentage(currentValue, previousValue),
                          tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                          comparisonDateLabel,
                        }
                      : {}

                    return {
                      label: displayName,
                      value: currentValue,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      tooltipTitle: displayName,
                      ...comparisonProps,
                    }
                  })
                })()}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Countries', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={(() => {
                  const totalVisitors = getTotalValue(topCountriesTotals?.visitors) || 1
                  return topCountriesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const comparisonProps = isCompareEnabled
                      ? {
                          ...calcPercentage(currentValue, previousValue),
                          tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                          comparisonDateLabel,
                        }
                      : {}

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
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      tooltipTitle: item.country_name || '',
                      ...comparisonProps,
                    }
                  })
                })()}
              />
            </div>

            {/* Row 6: Device Analytics (3 columns) */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Browsers', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={(() => {
                  const totalVisitors = getTotalValue(topBrowsersTotals?.visitors) || 1
                  return topBrowsersData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const iconName = (item.browser_name || 'unknown').toLowerCase()
                    const comparisonProps = isCompareEnabled
                      ? {
                          ...calcPercentage(currentValue, previousValue),
                          tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                          comparisonDateLabel,
                        }
                      : {}

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
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      tooltipTitle: item.browser_name || '',
                      ...comparisonProps,
                    }
                  })
                })()}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Operating Systems', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={(() => {
                  const totalVisitors = getTotalValue(topOSTotals?.visitors) || 1
                  return topOSData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const iconName = (item.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_')
                    const comparisonProps = isCompareEnabled
                      ? {
                          ...calcPercentage(currentValue, previousValue),
                          tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                          comparisonDateLabel,
                        }
                      : {}

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
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      tooltipTitle: item.os_name || '',
                      ...comparisonProps,
                    }
                  })
                })()}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Device Categories', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={(() => {
                  const totalVisitors = getTotalValue(topDevicesTotals?.visitors) || 1
                  return topDevicesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const iconName = (item.device_type_name || 'unknown').toLowerCase()
                    const comparisonProps = isCompareEnabled
                      ? {
                          ...calcPercentage(currentValue, previousValue),
                          tooltipSubtitle: `${__('Previous:', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                          comparisonDateLabel,
                        }
                      : {}

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
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      tooltipTitle: item.device_type_name || '',
                      ...comparisonProps,
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
