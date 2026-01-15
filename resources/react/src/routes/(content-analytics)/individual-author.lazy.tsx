import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, Link } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import { TabbedList, type TabbedListTab } from '@/components/custom/tabbed-list'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import {
  getIndividualAuthorQueryOptions,
  type TrafficSummaryPeriodResponse,
} from '@/services/content-analytics/get-individual-author'

export const Route = createLazyFileRoute('/(content-analytics)/individual-author')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  const { author_id } = Route.useSearch()

  // If no author_id, show error
  if (!author_id) {
    return (
      <div className="min-w-0 p-6">
        <div className="text-center">
          <h1 className="text-2xl font-semibold text-neutral-800 mb-2">{__('Author Not Found', 'wp-statistics')}</h1>
          <p className="text-muted-foreground mb-4">{__('No author ID was provided.', 'wp-statistics')}</p>
          <Link to="/authors" className="text-primary hover:underline">
            {__('Go to Authors Overview', 'wp-statistics')}
          </Link>
        </div>
      </div>
    )
  }

  return <IndividualAuthorView authorId={author_id} />
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
 * Individual Author View - Detailed view for a single author
 */
function IndividualAuthorView({ authorId }: { authorId: number }) {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    filters: appliedFilters,
    setDateRange,
    applyFilters: handleApplyFilters,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  // Get filter fields: individual-content group + post_type from content group
  const filterFields = useMemo<FilterField[]>(() => {
    const individualContentFilters = wp.getFilterFieldsByGroup('individual-content') as FilterField[]
    const contentFilters = wp.getFilterFieldsByGroup('content') as FilterField[]
    // Get post_type filter from content group
    const postTypeFilter = contentFilters.find((f) => f.name === 'post_type')
    // Combine: individual-content filters + post_type
    return postTypeFilter ? [...individualContentFilters, postTypeFilter] : individualContentFilters
  }, [wp])

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')
  const [activeContentTab, setActiveContentTab] = useState<string>('popular')
  const [defaultFilterRemoved, setDefaultFilterRemoved] = useState(false)

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

  // Build default post_type filter for Individual Author page (page-specific, not global)
  const defaultPostTypeFilter = useMemo(() => {
    const postTypeField = filterFields.find((f) => f.name === 'post_type')
    const postTypeOption = postTypeField?.options?.find((o) => o.value === 'post')

    return {
      id: 'post_type-individual-author-default',
      name: 'post_type',
      operator: 'is',
      value: postTypeOption?.label || __('Posts', 'wp-statistics'),
      rawValue: 'post',
      label: postTypeField?.label || __('Post Type', 'wp-statistics'),
    }
  }, [filterFields])

  // Get valid filter names for this page (based on filter groups)
  const validFilterNames = useMemo(() => {
    return new Set(filterFields.map((f) => f.name))
  }, [filterFields])

  // Filter applied filters to only include those valid for this page
  // This prevents filters from other pages from being applied here
  const normalizedFilters = useMemo(() => {
    return (appliedFilters || []).filter((f) => {
      // Extract filter name from filter id (e.g., "author-author-filter-123" -> "author")
      const filterName = f.id.split('-')[0]
      return validFilterNames.has(filterName)
    })
  }, [appliedFilters, validFilterNames])

  // Check if user has applied a post_type filter (overriding default)
  const hasUserPostTypeFilter = useMemo(() => {
    return normalizedFilters.some((f) => f.id.startsWith('post_type'))
  }, [normalizedFilters])

  // Reset defaultFilterRemoved when user applies a post_type filter
  useEffect(() => {
    if (hasUserPostTypeFilter) {
      setDefaultFilterRemoved(false)
    }
  }, [hasUserPostTypeFilter])

  // Filters to use for API requests (includes default if no user filter and not removed)
  const filtersForApi = useMemo(() => {
    if (hasUserPostTypeFilter) {
      return normalizedFilters
    }
    if (defaultFilterRemoved) {
      return normalizedFilters
    }
    return [...normalizedFilters, defaultPostTypeFilter]
  }, [normalizedFilters, hasUserPostTypeFilter, defaultPostTypeFilter, defaultFilterRemoved])

  // Filters to display (includes default if no user filter and not removed)
  const filtersForDisplay = useMemo(() => {
    if (hasUserPostTypeFilter) {
      return normalizedFilters
    }
    if (defaultFilterRemoved) {
      return normalizedFilters
    }
    return [...normalizedFilters, defaultPostTypeFilter]
  }, [normalizedFilters, hasUserPostTypeFilter, defaultPostTypeFilter, defaultFilterRemoved])

  // Wrap handleApplyFilters to detect when post_type filter is intentionally removed
  const handleAuthorApplyFilters = useCallback(
    (newFilters: typeof appliedFilters) => {
      // Check if post_type filter existed before but not in new filters
      const hadPostTypeFilter = filtersForDisplay.some((f) => f.id.startsWith('post_type'))
      const hasNewPostTypeFilter = newFilters?.some((f) => f.id.startsWith('post_type')) ?? false

      if (hadPostTypeFilter && !hasNewPostTypeFilter) {
        // User intentionally removed the post_type filter
        setDefaultFilterRemoved(true)
      }

      handleApplyFilters(newFilters)
    },
    [filtersForDisplay, handleApplyFilters]
  )

  // Get post_type from filters for API (includes default)
  const postType = useMemo(() => {
    const postTypeFilter = filtersForApi.find((f) => f.id.startsWith('post_type'))
    return postTypeFilter?.rawValue as string | undefined
  }, [filtersForApi])

  // Get post type label for display (includes default)
  const postTypeLabel = useMemo(() => {
    const postTypeFilter = filtersForApi.find((f) => f.id.startsWith('post_type'))
    if (postTypeFilter?.value) {
      return String(postTypeFilter.value)
    }
    return __('Content', 'wp-statistics')
  }, [filtersForApi])

  // Filters for API query (excludes author and post_type since they're handled separately)
  const filtersForApiQuery = useMemo(() => {
    return filtersForApi.filter((f) => !f.id.startsWith('post_type'))
  }, [filtersForApi])

  // Batch query for all individual author data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getIndividualAuthorQueryOptions({
      authorId,
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
      postType,
      filters: filtersForApiQuery,
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const showSkeleton = isLoading && !batchResponse
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Extract data from batch response
  const authorMetadata = batchResponse?.data?.items?.author_metadata?.data?.rows?.[0]
  const metricsResponse = batchResponse?.data?.items?.author_metrics
  const contentPerformanceResponse = batchResponse?.data?.items?.content_performance
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

  // Transform chart data using shared hook
  const { data: chartData, metrics: chartMetrics } = useChartData(contentPerformanceResponse, {
    metrics: [
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Build metrics - per documentation
  const authorMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const publishedContent = getTotalValue(totals.published_content)
    const visitors = getTotalValue(totals.visitors)
    const views = getTotalValue(totals.views)
    const bounceRate = getTotalValue(totals.bounce_rate)
    const avgTimeOnPage = getTotalValue(totals.avg_time_on_page)
    const comments = getTotalValue(totals.comments)

    const prevPublishedContent = getTotalValue(totals.published_content?.previous)
    const prevVisitors = getTotalValue(totals.visitors?.previous)
    const prevViews = getTotalValue(totals.views?.previous)
    const prevBounceRate = getTotalValue(totals.bounce_rate?.previous)
    const prevAvgTimeOnPage = getTotalValue(totals.avg_time_on_page?.previous)
    const prevComments = getTotalValue(totals.comments?.previous)

    // Calculate avg comments per content
    const avgCommentsPerContent = publishedContent > 0 ? comments / publishedContent : 0
    const prevAvgCommentsPerContent = prevPublishedContent > 0 ? prevComments / prevPublishedContent : 0

    const metrics: MetricItem[] = [
      {
        label: `${__('Published', 'wp-statistics')} ${postTypeLabel}`,
        value: formatCompactNumber(publishedContent),
        ...(isCompareEnabled ? calcPercentage(publishedContent, prevPublishedContent) : {}),
        tooltipContent: __('Number of published content items by this author', 'wp-statistics'),
      },
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...(isCompareEnabled ? calcPercentage(visitors, prevVisitors) : {}),
        tooltipContent: __("Unique visitors to this author's content", 'wp-statistics'),
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
        tooltipContent: __("Total comments on this author's content", 'wp-statistics'),
      },
      {
        label: `${__('Avg. Comments per', 'wp-statistics')} ${postTypeLabel}`,
        value: formatDecimal(avgCommentsPerContent),
        ...(isCompareEnabled ? calcPercentage(avgCommentsPerContent, prevAvgCommentsPerContent) : {}),
        tooltipContent: __('Average comments per content item', 'wp-statistics'),
      },
    ]

    return metrics
  }, [metricsResponse, calcPercentage, postTypeLabel, isCompareEnabled])

  // Build top content tabs
  const topContentTabs: TabbedListTab[] = useMemo(() => {
    const topContentPopular = batchResponse?.data?.items?.top_content_popular?.data?.rows || []
    const topContentRecent = batchResponse?.data?.items?.top_content_recent?.data?.rows || []
    const topContentCommented = batchResponse?.data?.items?.top_content_commented?.data?.rows || []

    // Build base URL params for "See all" links
    const dateParams = `date_from=${apiDateParams.date_from}&date_to=${apiDateParams.date_to}`
    const authorParam = `author=${authorId}`
    const postTypeParam = postType ? `&post_type=${postType}` : ''

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
        link: {
          title: `${__('See all', 'wp-statistics')} ${postTypeLabel}`,
          href: `/pages?${dateParams}&${authorParam}${postTypeParam}&order_by=views&order=desc`,
        },
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
        // No link for Most Commented per spec
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
        link: {
          title: `${__('See all', 'wp-statistics')} ${postTypeLabel}`,
          href: `/pages?${dateParams}&${authorParam}${postTypeParam}&order_by=date&order=desc`,
        },
      },
    ]

    return tabs
  }, [batchResponse, pluginUrl, apiDateParams, authorId, postType, postTypeLabel])

  // Format date for display
  const formatDate = (dateStr: string | null | undefined): string => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
  }

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="px-4 py-3 ">
        {/* Title row with filter and date picker */}
        <div className="flex items-center justify-between gap-4 mb-2">
          {/* Author Name with action icons */}
          <div className="flex items-center gap-3 min-w-0 flex-1">
            {/* Author Avatar */}
            {authorMetadata?.author_avatar && (
              <img
                src={authorMetadata.author_avatar}
                alt={authorMetadata.author_name || ''}
                className="w-10 h-10 rounded-full flex-shrink-0"
              />
            )}
            <div className="min-w-0">
              <div className="flex items-center gap-2">
                <h1 className="text-lg font-semibold text-neutral-800 truncate">
                  {authorMetadata?.author_name || __('Unknown Author', 'wp-statistics')}
                </h1>
                {authorMetadata?.author_profile_url && (
                  <a
                    href={authorMetadata.author_profile_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-muted-foreground hover:text-foreground flex-shrink-0"
                    title={__('View profile', 'wp-statistics')}
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
                {authorMetadata?.author_posts_url && (
                  <a
                    href={authorMetadata.author_posts_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-muted-foreground hover:text-foreground flex-shrink-0"
                    title={__('View author posts', 'wp-statistics')}
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                      />
                    </svg>
                  </a>
                )}
              </div>
            </div>
          </div>
          <div className="flex items-center gap-3 flex-shrink-0">
            {filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={filtersForDisplay}
                onApplyFilters={handleAuthorApplyFilters}
                filterGroup="individual-content"
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

        {/* Author Meta */}
        {authorMetadata && (
          <div className="flex items-center gap-3 text-sm text-muted-foreground">
            {/* Email */}
            {authorMetadata.author_email && <span>{authorMetadata.author_email}</span>}

            {/* Role */}
            {authorMetadata.author_role && (
              <>
                {authorMetadata.author_email && <span className="text-muted-foreground/50">•</span>}
                <span className="capitalize">{authorMetadata.author_role}</span>
              </>
            )}

            {/* Registration Date */}
            {authorMetadata.author_registered && (
              <>
                <span className="text-muted-foreground/50">•</span>
                <span>
                  {__('Registered:', 'wp-statistics')} {formatDate(authorMetadata.author_registered)}
                </span>
              </>
            )}
          </div>
        )}
      </div>

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="individual-author" />

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
            {/* Row 1: Individual Author Metrics */}
            <div className="col-span-12">
              <Panel>
                <Metrics metrics={authorMetrics} columns={4} />
              </Panel>
            </div>

            {/* Row 2: Traffic Summary (1/3) + Content Performance (2/3) */}
            <div className="col-span-12 lg:col-span-4">
              <TrafficSummaryTable data={trafficSummaryData} />
            </div>
            <div className="col-span-12 lg:col-span-8">
              <LineChart
                title={__('Content Performance', 'wp-statistics')}
                data={chartData}
                metrics={chartMetrics}
                showPreviousPeriod={isCompareEnabled}
                timeframe={timeframe}
                onTimeframeChange={handleTimeframeChange}
                loading={isChartRefetching}
                dateTo={apiDateParams.date_to}
                compareDateTo={apiDateParams.previous_date_to}
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

            {/* Row 4: Traffic Sources (3 columns) */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Referrers', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topReferrersData, {
                  label: (item) => item.referrer_name || item.referrer_domain || __('Direct', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: getTotalValue(topReferrersTotals?.visitors) || 1,
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Search Engines', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topSearchEnginesData, {
                  label: (item) => item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: getTotalValue(topSearchEnginesTotals?.visitors) || 1,
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Countries', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topCountriesData, {
                  label: (item) => item.country_name || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: getTotalValue(topCountriesTotals?.visitors) || 1,
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
                      alt={item.country_name || ''}
                      className="w-4 h-3"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>

            {/* Row 5: Device Analytics (3 columns) */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Browsers', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topBrowsersData, {
                  label: (item) => item.browser_name || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: getTotalValue(topBrowsersTotals?.visitors) || 1,
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/browser/${(item.browser_name || 'unknown').toLowerCase()}.svg`}
                      alt={item.browser_name || ''}
                      className="w-4 h-3"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Operating Systems', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topOSData, {
                  label: (item) => item.os_name || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: getTotalValue(topOSTotals?.visitors) || 1,
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/operating-system/${(item.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
                      alt={item.os_name || ''}
                      className="w-4 h-3"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Device Categories', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topDevicesData, {
                  label: (item) => item.device_type_name || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: getTotalValue(topDevicesTotals?.visitors) || 1,
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/device/${(item.device_type_name || 'desktop').toLowerCase()}.svg`}
                      alt={item.device_type_name || ''}
                      className="w-4 h-3"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
              />
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
