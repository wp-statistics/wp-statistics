import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { BarListContent } from '@/components/custom/bar-list-content'
import { DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBar } from '@/components/custom/horizontal-bar'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import {
  OptionsDrawerTrigger,
  type OverviewOptionsConfig,
  OverviewOptionsDrawer,
  OverviewOptionsProvider,
  useOverviewOptions,
} from '@/components/custom/options-drawer'
import { TabbedPanel, type TabbedPanelTab } from '@/components/custom/tabbed-panel'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { pickMetrics } from '@/constants/metric-definitions'
import { type WidgetConfig } from '@/contexts/page-options-context'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { extractFilterField, getCompatibleFilters } from '@/lib/filter-utils'
import { getAnalyticsRoute } from '@/lib/url-utils'
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getContentOverviewQueryOptions, type TopContentItem } from '@/services/content-analytics/get-content-overview'

// Widget configuration for Content page
const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'traffic-trends', label: __('Content Performance', 'wp-statistics'), defaultVisible: true },
  { id: 'top-content', label: __('Top Content', 'wp-statistics'), defaultVisible: true },
  { id: 'top-referrers', label: __('Top Referrers', 'wp-statistics'), defaultVisible: true },
  { id: 'top-search-engines', label: __('Top Search Engines', 'wp-statistics'), defaultVisible: true },
  { id: 'top-countries', label: __('Top Countries', 'wp-statistics'), defaultVisible: true },
  { id: 'top-browsers', label: __('Top Browsers', 'wp-statistics'), defaultVisible: true },
  { id: 'top-operating-systems', label: __('Top Operating Systems', 'wp-statistics'), defaultVisible: true },
  { id: 'top-device-categories', label: __('Top Device Categories', 'wp-statistics'), defaultVisible: true },
]

// Metric configuration for Content page (8 metrics)
const METRIC_CONFIGS = pickMetrics('publishedContent', 'visitors', 'views', 'viewsPerContent', 'bounceRate', 'avgTimeOnPage', 'comments', 'avgCommentsPerContent')

// Options configuration for this page
const OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'content-overview',
  filterGroup: 'content',
  widgetConfigs: WIDGET_CONFIGS,
  metricConfigs: METRIC_CONFIGS,
}

export const Route = createLazyFileRoute('/(content-analytics)/content')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  return (
    <OverviewOptionsProvider config={OPTIONS_CONFIG}>
      <ContentOverviewContent />
    </OverviewOptionsProvider>
  )
}

/**
 * Content Overview View - Main content analytics page
 */
function ContentOverviewContent() {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    filters: appliedFilters,
    handleDateRangeUpdate,
    applyFilters: handleApplyFilters,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  // Page options for metric and widget visibility
  const { isMetricVisible, isWidgetVisible } = usePageOptions()

  // Options drawer (uses new reusable components)
  const options = useOverviewOptions(OPTIONS_CONFIG)

  const wp = WordPress.getInstance()

  // Get filter fields for content analytics
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('content') as FilterField[]
  }, [wp])

  // Filter to only include compatible filters for this page
  const compatibleFilters = useMemo(() => {
    return getCompatibleFilters(appliedFilters || [], filterFields)
  }, [appliedFilters, filterFields])

  // Chart timeframe state
  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Track if only timeframe changed (for loading behavior)
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevFiltersRef = useRef<string>(JSON.stringify(appliedFilters))
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  // Detect what changed when data updates
  useEffect(() => {
    const filtersChanged = JSON.stringify(appliedFilters) !== prevFiltersRef.current
    const dateRangeChanged = dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current

    // If filters or dates changed, it's NOT a timeframe-only change
    if (filtersChanged || dateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    // Update refs
    prevFiltersRef.current = JSON.stringify(appliedFilters)
    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [appliedFilters, dateFrom, dateTo])

  // Custom timeframe setter that tracks the change type
  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  const [defaultFilterRemoved, setDefaultFilterRemoved] = useState(false)

  // Build default post_type filter for Content page (page-specific, not global)
  const defaultPostTypeFilter = useMemo(() => {
    const postTypeField = filterFields.find((f) => f.name === 'post_type')
    const postTypeOption = postTypeField?.options?.find((o) => o.value === 'post')

    return {
      id: 'post_type-content-default',
      label: postTypeField?.label || __('Post Type', 'wp-statistics'),
      operator: '=',
      rawOperator: 'is',
      value: postTypeOption?.label || __('Post', 'wp-statistics'),
      rawValue: 'post',
    }
  }, [filterFields])

  // Check if user has applied a post_type filter (overriding default)
  const hasUserPostTypeFilter = useMemo(() => {
    return compatibleFilters.some((f) => f.id.startsWith('post_type'))
  }, [compatibleFilters])

  // Reset defaultFilterRemoved when user applies a post_type filter
  useEffect(() => {
    if (hasUserPostTypeFilter) {
      setDefaultFilterRemoved(false)
    }
  }, [hasUserPostTypeFilter])

  // Filters to use for API requests (includes default if no user filter and not removed)
  const filtersForApi = useMemo(() => {
    if (hasUserPostTypeFilter) {
      return compatibleFilters
    }
    if (defaultFilterRemoved) {
      return compatibleFilters
    }
    return [...compatibleFilters, defaultPostTypeFilter]
  }, [compatibleFilters, hasUserPostTypeFilter, defaultPostTypeFilter, defaultFilterRemoved])

  // Filters to display (includes default if no user filter and not removed)
  const filtersForDisplay = useMemo(() => {
    if (hasUserPostTypeFilter) {
      return compatibleFilters
    }
    if (defaultFilterRemoved) {
      return compatibleFilters
    }
    return [...compatibleFilters, defaultPostTypeFilter]
  }, [compatibleFilters, hasUserPostTypeFilter, defaultPostTypeFilter, defaultFilterRemoved])

  // Helper to check if a filter is the default post_type filter by field name + value
  const isDefaultPostTypeFilter = useCallback((filter: { id: string; rawValue?: unknown; value: unknown }) => {
    const fieldName = extractFilterField(filter.id)
    if (fieldName !== 'post_type') return false
    const value = filter.rawValue ?? filter.value
    return value === 'post'
  }, [])

  // Wrap handleApplyFilters to detect when post_type filter is intentionally removed
  const handleContentApplyFilters = useCallback(
    (newFilters: typeof appliedFilters) => {
      // Check if post_type filter existed before but not in new filters
      const hadPostTypeFilter = filtersForDisplay.some((f) => f.id.startsWith('post_type'))
      const hasNewPostTypeFilter = newFilters?.some((f) => f.id.startsWith('post_type')) ?? false

      if (hadPostTypeFilter && !hasNewPostTypeFilter) {
        // User intentionally removed the post_type filter
        setDefaultFilterRemoved(true)
      }

      // Apply only the non-default filters to global state (check by field+value, not ID)
      const globalFilters = newFilters?.filter((f) => !isDefaultPostTypeFilter(f)) ?? []
      handleApplyFilters(globalFilters)
    },
    [filtersForDisplay, handleApplyFilters, isDefaultPostTypeFilter]
  )


  // Batch query for all overview data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getContentOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      filters: filtersForApi,
      timeframe,
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Only show skeleton on initial load (no data yet), not on refetches
  const showSkeleton = isLoading && !batchResponse
  // Show full page loading when filters/dates change (not timeframe-only)
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  // Show loading indicator on chart only when timeframe changes
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Extract data from batch response
  const metricsResponse = batchResponse?.data?.items?.content_metrics
  const trafficTrendsResponse = batchResponse?.data?.items?.traffic_trends
  const topContentResponse = batchResponse?.data?.items?.top_content
  const topReferrersResponse = batchResponse?.data?.items?.top_referrers
  const topSearchEnginesResponse = batchResponse?.data?.items?.top_search_engines
  const topCountriesResponse = batchResponse?.data?.items?.top_countries
  const topBrowsersResponse = batchResponse?.data?.items?.top_browsers
  const topOperatingSystemsResponse = batchResponse?.data?.items?.top_operating_systems
  const topDeviceCategoriesResponse = batchResponse?.data?.items?.top_device_categories

  // Get plugin URL for icons
  const pluginUrl = wp.getPluginUrl()

  // Transform chart data using shared hook
  // Uses 'published_content' source which counts posts by publish date from wp_posts
  const { data: chartData, metrics: chartMetrics } = useChartData(trafficTrendsResponse, {
    metrics: [
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
      { key: 'published_content', label: __('Published Content', 'wp-statistics'), color: 'var(--chart-3)', type: 'bar' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Get post type label from filters (includes page-specific default)
  const postTypeLabel = useMemo(() => {
    // Look for post_type filter in filters for API (includes default)
    const postTypeFilter = filtersForApi.find((f) => f.id.startsWith('post_type'))
    if (postTypeFilter?.value) {
      // Return the display value which is the label
      return String(postTypeFilter.value)
    }
    return __('Content', 'wp-statistics')
  }, [filtersForApi])

  // Build metrics with visibility filtering
  const contentMetrics = useMemo(() => {
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

    const viewsPerPost = publishedContent > 0 ? views / publishedContent : 0
    const prevViewsPerPost = prevPublishedContent > 0 ? prevViews / prevPublishedContent : 0

    const avgCommentsPerPost = publishedContent > 0 ? comments / publishedContent : 0
    const prevAvgCommentsPerPost = prevPublishedContent > 0 ? prevComments / prevPublishedContent : 0

    // Build all metrics with IDs for filtering
    const allMetrics: (MetricItem & { id: string })[] = [
      {
        id: 'published-content',
        label: `${__('Published', 'wp-statistics')} ${postTypeLabel}`,
        value: formatCompactNumber(publishedContent),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(publishedContent, prevPublishedContent),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevPublishedContent),
            }
          : {}),
      },
      {
        id: 'visitors',
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(visitors, prevVisitors),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevVisitors),
            }
          : {}),
      },
      {
        id: 'views',
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(views, prevViews),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevViews),
            }
          : {}),
      },
      {
        id: 'views-per-content',
        label: `${__('Views per', 'wp-statistics')} ${postTypeLabel}`,
        value: formatDecimal(viewsPerPost),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(viewsPerPost, prevViewsPerPost),
              comparisonDateLabel,
              previousValue: formatDecimal(prevViewsPerPost),
            }
          : {}),
      },
      {
        id: 'bounce-rate',
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(bounceRate)}%`,
        ...(isCompareEnabled
          ? {
              ...calcPercentage(bounceRate, prevBounceRate),
              comparisonDateLabel,
              previousValue: `${formatDecimal(prevBounceRate)}%`,
            }
          : {}),
      },
      {
        id: 'avg-time-on-page',
        label: __('Avg. Time on Page', 'wp-statistics'),
        value: formatDuration(avgTimeOnPage),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(avgTimeOnPage, prevAvgTimeOnPage),
              comparisonDateLabel,
              previousValue: formatDuration(prevAvgTimeOnPage),
            }
          : {}),
      },
      {
        id: 'comments',
        label: __('Comments', 'wp-statistics'),
        value: formatCompactNumber(comments),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(comments, prevComments),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevComments),
            }
          : {}),
      },
      {
        id: 'avg-comments-per-content',
        label: `${__('Avg. Comments per', 'wp-statistics')} ${postTypeLabel}`,
        value: formatDecimal(avgCommentsPerPost),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(avgCommentsPerPost, prevAvgCommentsPerPost),
              comparisonDateLabel,
              previousValue: formatDecimal(prevAvgCommentsPerPost),
            }
          : {}),
      },
    ]

    // Filter metrics based on visibility
    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [metricsResponse, postTypeLabel, calcPercentage, isCompareEnabled, comparisonDateLabel, isMetricVisible])

  // Transform top content data into tabbed panel format with HorizontalBar
  const topContentTabs = useMemo((): TabbedPanelTab[] => {
    const rows = topContentResponse?.data?.rows || []

    // Build URL params for links
    const baseParams = `date_from=${apiDateParams.date_from}&date_to=${apiDateParams.date_to}`

    // Most Popular: Sort by views desc
    const popularSorted = [...rows].sort((a, b) => Number(b.views) - Number(a.views)).slice(0, 5)
    const maxPopularViews = popularSorted[0] ? Number(popularSorted[0].views) : 1

    // Most Commented: Filter items with comments > 0, then sort by comments desc
    const commentedSorted = [...rows]
      .filter((item) => Number(item.comments) > 0)
      .sort((a, b) => Number(b.comments) - Number(a.comments))
      .slice(0, 5)
    const maxComments = commentedSorted[0] ? Number(commentedSorted[0].comments) : 1

    // Most Recent: Sort by published_date desc
    const recentSorted = [...rows]
      .filter((item) => item.published_date)
      .sort((a, b) => {
        const dateA = new Date(a.published_date || 0).getTime()
        const dateB = new Date(b.published_date || 0).getTime()
        return dateB - dateA
      })
      .slice(0, 5)

    // Build tabs array
    const tabs: TabbedPanelTab[] = [
      {
        id: 'popular',
        label: __('Most Popular', 'wp-statistics'),
        columnHeaders: {
          left: __('Content', 'wp-statistics'),
          right: __('Views', 'wp-statistics'),
        },
        content: (
          <BarListContent isEmpty={popularSorted.length === 0}>
            {popularSorted.map((item, i) => {
              const views = Number(item.views) || 0
              const prevViews = Number(item.previous?.views) || 0
              const comparison = isCompareEnabled ? calcPercentage(views, prevViews) : null
              const route = getAnalyticsRoute(item.page_type, item.page_wp_id)

              return (
                <HorizontalBar
                  key={`${item.page_uri}-${i}`}
                  label={item.page_title || item.page_uri || '/'}
                  value={`${formatCompactNumber(views)} ${__('views', 'wp-statistics')}`}
                  percentage={comparison?.percentage}
                  isNegative={comparison?.isNegative}
                  tooltipSubtitle={isCompareEnabled ? `${__('Previous:', 'wp-statistics')} ${formatCompactNumber(prevViews)}` : undefined}
                  comparisonDateLabel={comparisonDateLabel}
                  showComparison={isCompareEnabled}
                  showBar={false}
                  highlightFirst={false}
                  linkTo={route?.to}
                  linkParams={route?.params}
                />
              )
            })}
          </BarListContent>
        ),
        link: {
          href: `/top-pages?order_by=views&order=desc&${baseParams}`,
          title: __('See all', 'wp-statistics'),
        },
      },
    ]

    // Only add "Most Commented" tab if there are items with comments
    if (commentedSorted.length > 0) {
      tabs.push({
        id: 'commented',
        label: __('Most Commented', 'wp-statistics'),
        columnHeaders: {
          left: __('Content', 'wp-statistics'),
          right: __('Comments', 'wp-statistics'),
        },
        content: (
          <BarListContent>
            {commentedSorted.map((item, i) => {
              const route = getAnalyticsRoute(item.page_type, item.page_wp_id)
              return (
                <HorizontalBar
                  key={`${item.page_uri}-${i}`}
                  label={item.page_title || item.page_uri || '/'}
                  value={`${formatCompactNumber(Number(item.comments))} ${__('comments', 'wp-statistics')}`}
                  showComparison={false}
                  showBar={false}
                  highlightFirst={false}
                  linkTo={route?.to}
                  linkParams={route?.params}
                />
              )
            })}
          </BarListContent>
        ),
        // No link for Most Commented tab
      })
    }

    tabs.push({
      id: 'recent',
      label: __('Most Recent', 'wp-statistics'),
      columnHeaders: {
        left: __('Content', 'wp-statistics'),
        right: __('Views', 'wp-statistics'),
      },
      content: (
        <BarListContent isEmpty={recentSorted.length === 0}>
          {recentSorted.map((item, i) => {
            const route = getAnalyticsRoute(item.page_type, item.page_wp_id)
            return (
              <HorizontalBar
                key={`${item.page_uri}-${i}`}
                label={item.page_title || item.page_uri || '/'}
                value={`${formatCompactNumber(Number(item.views))} ${__('views', 'wp-statistics')}`}
                showComparison={false}
                showBar={false}
                highlightFirst={false}
                linkTo={route?.to}
                linkParams={route?.params}
              />
            )
          })}
        </BarListContent>
      ),
      link: {
        href: `/top-pages?order_by=publishedDate&order=desc&${baseParams}`,
        title: __('See all', 'wp-statistics'),
      },
    })

    return tabs
  }, [topContentResponse, apiDateParams.date_from, apiDateParams.date_to, isCompareEnabled, calcPercentage, comparisonDateLabel])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Content', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          <div className="hidden lg:flex">
            {filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={filtersForDisplay}
                onApplyFilters={handleContentApplyFilters}
                filterGroup="content"
              />
            )}
          </div>
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
          <OptionsDrawerTrigger {...options.triggerProps} />
        </div>
      </div>

      {/* Options Drawer */}
      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="content" />

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={8} columns={4} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12">
              <ChartSkeleton />
            </div>
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Content Metrics */}
            {contentMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={contentMetrics} columns="auto" />
                </Panel>
              </div>
            )}
            {/* Row 2: Content Performance Chart */}
            {isWidgetVisible('traffic-trends') && (
              <div className="col-span-12">
                <LineChart
                  title={__('Content Performance', 'wp-statistics')}
                  data={chartData}
                  metrics={chartMetrics}
                  showPreviousPeriod={isCompareEnabled}
                  timeframe={timeframe}
                  onTimeframeChange={handleTimeframeChange}
                  loading={isChartRefetching}
                  compareDateTo={apiDateParams.previous_date_to}
                  dateTo={apiDateParams.date_to}
                />
              </div>
            )}
            {/* Row 3: Top Content Widget */}
            {isWidgetVisible('top-content') && (
              <div className="col-span-12">
                <TabbedPanel
                  title={__('Top Content', 'wp-statistics')}
                  tabs={topContentTabs}
                  defaultTab="popular"
                />
              </div>
            )}
            {/* Row 4: Top Referrers & Top Search Engines */}
            {isWidgetVisible('top-referrers') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Referrers', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Referrer', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topReferrersResponse?.data?.rows || [], {
                    label: (item) => item.referrer_name || item.referrer_domain || __('Direct', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topReferrersResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                />
              </div>
            )}
            {isWidgetVisible('top-search-engines') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Search Engines', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Search Engine', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topSearchEnginesResponse?.data?.rows || [], {
                    label: (item) => item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topSearchEnginesResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                />
              </div>
            )}
            {/* Row 5: Top Countries & Top Browsers */}
            {isWidgetVisible('top-countries') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Countries', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Country', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topCountriesResponse?.data?.rows || [], {
                    label: (item) => item.country_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topCountriesResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                    icon: (item) => (
                      <img
                        src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
                        alt={item.country_name || ''}
                        className="h-4 w-4 shrink-0"
                      />
                    ),
                    linkTo: () => '/country/$countryCode',
                    linkParams: (item) => ({ countryCode: item.country_code?.toLowerCase() || '000' }),
                  })}
                />
              </div>
            )}
            {isWidgetVisible('top-browsers') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Browsers', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Browser', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topBrowsersResponse?.data?.rows || [], {
                    label: (item) => item.browser_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topBrowsersResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                    icon: (item) => (
                      <img
                        src={`${pluginUrl}public/images/browser/${(item.browser_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
                        alt={item.browser_name || ''}
                        className="h-4 w-4 shrink-0"
                      />
                    ),
                  })}
                />
              </div>
            )}
            {/* Row 6: Top Operating Systems & Top Device Categories */}
            {isWidgetVisible('top-operating-systems') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Operating Systems', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('OS', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topOperatingSystemsResponse?.data?.rows || [], {
                    label: (item) => item.os_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topOperatingSystemsResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                    icon: (item) => (
                      <img
                        src={`${pluginUrl}public/images/operating-system/${(item.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
                        alt={item.os_name || ''}
                        className="h-4 w-4 shrink-0"
                      />
                    ),
                  })}
                />
              </div>
            )}
            {isWidgetVisible('top-device-categories') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Device Categories', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Device', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topDeviceCategoriesResponse?.data?.rows || [], {
                    label: (item) => item.device_type_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topDeviceCategoriesResponse?.data?.totals?.visitors?.current) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                    icon: (item) => (
                      <img
                        src={`${pluginUrl}public/images/device/${(item.device_type_name || 'desktop').toLowerCase()}.svg`}
                        alt={item.device_type_name || ''}
                        className="h-4 w-4 shrink-0"
                      />
                    ),
                  })}
                />
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  )
}
