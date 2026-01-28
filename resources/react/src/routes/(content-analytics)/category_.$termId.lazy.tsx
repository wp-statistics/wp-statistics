import { keepPreviousData, useQueries, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, Link } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { ArrowLeft, LockIcon } from 'lucide-react'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DateRangePicker } from '@/components/custom/date-range-picker'
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
import { SimpleTable, type SimpleTableColumn } from '@/components/custom/simple-table'
import { TabbedPanel, type TabbedPanelTab } from '@/components/custom/tabbed-panel'
import { NumericCell } from '@/components/data-table-columns'
import { EmptyState } from '@/components/ui/empty-state'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { pickMetrics } from '@/constants/metric-definitions'
import { type WidgetConfig } from '@/contexts/page-options-context'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { usePremiumFeature } from '@/hooks/use-premium-feature'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { getFixedDatePeriods } from '@/lib/fixed-date-ranges'
import { getAnalyticsRoute } from '@/lib/url-utils'
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import {
  type ContentRow,
  extractCategoryTrafficSummaryData,
  extractTermMetrics,
  getCategoryTrafficSummaryPeriodQueryOptions,
  getSingleCategoryQueryOptions,
} from '@/services/content-analytics/get-single-category'

// Widget configuration for Single Category Report
const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'metrics', label: __('Metrics Overview', 'wp-statistics'), defaultVisible: true },
  { id: 'traffic-trends', label: __('Performance', 'wp-statistics'), defaultVisible: true },
  { id: 'traffic-summary', label: __('Traffic Summary', 'wp-statistics'), defaultVisible: true },
  { id: 'top-content', label: __('Top Content', 'wp-statistics'), defaultVisible: true },
  { id: 'top-referrers', label: __('Top Referrers', 'wp-statistics'), defaultVisible: true },
  { id: 'top-search-engines', label: __('Top Search Engines', 'wp-statistics'), defaultVisible: true },
  { id: 'top-countries', label: __('Top Countries', 'wp-statistics'), defaultVisible: true },
  { id: 'top-browsers', label: __('Top Browsers', 'wp-statistics'), defaultVisible: true },
  { id: 'top-operating-systems', label: __('Top Operating Systems', 'wp-statistics'), defaultVisible: true },
  { id: 'top-device-categories', label: __('Top Device Categories', 'wp-statistics'), defaultVisible: true },
]

// Metric configuration for Single Category Report (5 metrics)
const METRIC_CONFIGS = pickMetrics('contents', 'visitors', 'views', 'bounceRate', 'avgTimeOnPage')

// Options configuration (shared pageId for all single category reports)
const OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'single-category',
  filterGroup: 'individual-category',
  widgetConfigs: WIDGET_CONFIGS,
  metricConfigs: METRIC_CONFIGS,
}

// Traffic Summary table row data structure
interface TrafficSummaryRow {
  label: string
  visitors: number
  views: number
  previousVisitors?: number
  previousViews?: number
  comparisonLabel?: string
}

// Traffic Summary table columns
const trafficSummaryColumns: SimpleTableColumn<TrafficSummaryRow>[] = [
  {
    key: 'period',
    header: __('Time Period', 'wp-statistics'),
    cell: (row) => <span className="font-medium">{row.label}</span>,
  },
  {
    key: 'visitors',
    header: __('Visitors', 'wp-statistics'),
    align: 'right',
    cell: (row) => (
      <NumericCell
        value={row.visitors}
        previousValue={row.previousVisitors}
        comparisonLabel={row.comparisonLabel}
      />
    ),
  },
  {
    key: 'views',
    header: __('Views', 'wp-statistics'),
    align: 'right',
    cell: (row) => (
      <NumericCell
        value={row.views}
        previousValue={row.previousViews}
        comparisonLabel={row.comparisonLabel}
      />
    ),
  },
]

export const Route = createLazyFileRoute('/(content-analytics)/category_/$termId')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

/**
 * Locked state for custom taxonomies without premium
 */
function LockedState({ taxonomyType }: { taxonomyType: string }) {
  return (
    <Panel className="p-8 text-center">
      <div className="max-w-md mx-auto space-y-4">
        <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
          <LockIcon className="w-8 h-8 text-primary" strokeWidth={1.5} />
        </div>
        <h2 className="text-lg font-semibold text-neutral-800">
          {__('Custom Taxonomy Analytics', 'wp-statistics')}
        </h2>
        <p className="text-sm text-muted-foreground">
          {__(
            'View detailed analytics for custom taxonomies like',
            'wp-statistics'
          )}{' '}
          <span className="font-medium">{taxonomyType}</span>.{' '}
          {__(
            'Understand how your custom taxonomy terms perform with visitors, views, engagement metrics, and more.',
            'wp-statistics'
          )}
        </p>
        <p className="text-sm text-muted-foreground">
          {__('This feature requires the Premium addon with Custom Taxonomy Support.', 'wp-statistics')}
        </p>
        <a
          href="https://wp-statistics.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=custom-taxonomy-support"
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 transition-colors"
        >
          {__('Upgrade to Premium', 'wp-statistics')}
        </a>
      </div>
    </Panel>
  )
}

function RouteComponent() {
  return (
    <OverviewOptionsProvider config={OPTIONS_CONFIG}>
      <SingleCategoryReportContent />
    </OverviewOptionsProvider>
  )
}

function SingleCategoryReportContent() {
  const { termId } = Route.useParams()

  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    handleDateRangeUpdate,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  // Page options for widget/metric visibility
  const { isWidgetVisible, isMetricVisible } = usePageOptions()

  // Options drawer (uses reusable components)
  const options = useOverviewOptions(OPTIONS_CONFIG)

  const wp = WordPress.getInstance()

  // Check if custom taxonomy support is unlocked
  const { isEnabled: isCustomTaxonomyEnabled } = usePremiumFeature('custom-taxonomy-support')

  // Chart timeframe state
  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Track if only timeframe changed (for loading behavior)
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  // Detect what changed when data updates
  useEffect(() => {
    const dateRangeChanged = dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current

    // If dates changed, it's NOT a timeframe-only change
    if (dateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    // Update refs
    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [dateFrom, dateTo])

  // Custom timeframe setter that tracks the change type
  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  // Batch query for single category data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getSingleCategoryQueryOptions({
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

  // Get fixed date periods for traffic summary
  const fixedDatePeriods = useMemo(() => getFixedDatePeriods(), [])

  // Parallel queries for traffic summary data (5 fixed time periods)
  const trafficSummaryQueries = useQueries({
    queries: fixedDatePeriods.map((period) => ({
      ...getCategoryTrafficSummaryPeriodQueryOptions({
        termId,
        period,
      }),
      enabled: isInitialized,
      placeholderData: keepPreviousData,
    })),
  })

  // Check if traffic summary is loading
  const isTrafficSummaryLoading = trafficSummaryQueries.some((q) => q.isLoading)

  // Transform traffic summary data for display
  const trafficSummaryData = useMemo<TrafficSummaryRow[]>(() => {
    return fixedDatePeriods.map((period, index) => {
      const queryResult = trafficSummaryQueries[index]
      const data = extractCategoryTrafficSummaryData(queryResult.data, period.id)

      return {
        label: period.label,
        visitors: data?.visitors ?? 0,
        views: data?.views ?? 0,
        previousVisitors: data?.previous?.visitors,
        previousViews: data?.previous?.views,
        comparisonLabel: period.comparisonLabel,
      }
    })
  // eslint-disable-next-line @tanstack/query/no-unstable-deps -- Intentionally using entire useQueries result for transformation
  }, [fixedDatePeriods, trafficSummaryQueries])

  // Extract data from batch response
  const categoryInfoResponse = batchResponse?.data?.items?.category_info
  const categoryMetricsResponse = batchResponse?.data?.items?.category_metrics
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

  // Transform chart data using shared hook (visitors + views only for single category)
  const { data: chartData, metrics: chartMetrics } = useChartData(trafficTrendsResponse, {
    metrics: [
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  // Extract the specific term's data from the category_info response
  const termData = useMemo(
    () => extractTermMetrics(categoryInfoResponse, termId),
    [categoryInfoResponse, termId]
  )

  // Get category title from term data
  const categoryTitle = termData?.term_name || __('Category', 'wp-statistics')
  const taxonomyType = termData?.taxonomy_type || 'category'

  // Check if this is a built-in taxonomy (category or post_tag)
  const isBuiltInTaxonomy = taxonomyType === 'category' || taxonomyType === 'post_tag'

  // Show locked state for custom taxonomies without premium
  const showLockedState = !isBuiltInTaxonomy && !isCustomTaxonomyEnabled

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Only show skeleton on initial load (no data yet), not on refetches
  const showSkeleton = isLoading && !batchResponse
  // Show full page loading when dates change (not timeframe-only)
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  // Show loading indicator on chart only when timeframe changes
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Build metrics from category_metrics response (flat format with totals)
  // Always show metrics panel with zeros when no data
  const categoryMetrics = useMemo(() => {
    const totals = categoryMetricsResponse?.totals

    const publishedContent = getTotalValue(totals?.published_content?.current) || 0
    const visitors = getTotalValue(totals?.visitors?.current) || 0
    const views = getTotalValue(totals?.views?.current) || 0
    const bounceRate = getTotalValue(totals?.bounce_rate?.current) || 0
    const avgTimeOnPage = getTotalValue(totals?.avg_time_on_page?.current) || 0

    const prevPublishedContent = getTotalValue(totals?.published_content?.previous) || 0
    const prevVisitors = getTotalValue(totals?.visitors?.previous) || 0
    const prevViews = getTotalValue(totals?.views?.previous) || 0
    const prevBounceRate = getTotalValue(totals?.bounce_rate?.previous) || 0
    const prevAvgTimeOnPage = getTotalValue(totals?.avg_time_on_page?.previous) || 0

    // Build all metrics with IDs for filtering
    const allMetrics: (MetricItem & { id: string })[] = [
      {
        id: 'contents',
        label: __('Contents', 'wp-statistics'),
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
    ]

    // Filter metrics based on visibility
    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [categoryMetricsResponse, calcPercentage, isCompareEnabled, comparisonDateLabel, isMetricVisible])

  // Get content rows from response
  const contentRows = useMemo(() => {
    return topContentResponse?.data?.rows || []
  }, [topContentResponse])

  // Transform top content data into tabbed panel format
  const topContentTabs = useMemo((): TabbedPanelTab[] => {
    // Most Popular: Sort by views desc
    const popularSorted = [...contentRows].sort((a, b) => Number(b.views) - Number(a.views)).slice(0, 5)

    // Most Commented: Filter items with comments > 0, then sort by comments desc
    const commentedSorted = [...contentRows]
      .filter((item) => Number(item.comments) > 0)
      .sort((a, b) => Number(b.comments) - Number(a.comments))
      .slice(0, 5)

    // Most Recent: Sort by published_date desc
    const recentSorted = [...contentRows]
      .filter((item) => item.published_date)
      .sort((a, b) => {
        const dateA = new Date(a.published_date || 0).getTime()
        const dateB = new Date(b.published_date || 0).getTime()
        return dateB - dateA
      })
      .slice(0, 5)

    const renderContentItem = (item: ContentRow, showComparison: boolean = true) => {
      const views = Number(item.views) || 0
      const prevViews = Number(item.previous?.views) || 0
      const comparison = isCompareEnabled && showComparison ? calcPercentage(views, prevViews) : null
      const route = getAnalyticsRoute(item.page_type, item.page_wp_id)

      return (
        <HorizontalBar
          key={`${item.page_uri}-${item.page_wp_id}`}
          label={item.page_title || item.page_uri || '/'}
          value={`${formatCompactNumber(views)} ${__('views', 'wp-statistics')}`}
          percentage={comparison?.percentage}
          isNegative={comparison?.isNegative}
          tooltipSubtitle={
            isCompareEnabled && showComparison ? `${__('Previous:', 'wp-statistics')} ${formatCompactNumber(prevViews)}` : undefined
          }
          comparisonDateLabel={comparisonDateLabel}
          showComparison={isCompareEnabled && showComparison}
          showBar={false}
          highlightFirst={false}
          linkTo={route?.to}
          linkParams={route?.params}
        />
      )
    }

    const tabs: TabbedPanelTab[] = [
      {
        id: 'popular',
        label: __('Most Popular', 'wp-statistics'),
        columnHeaders: {
          left: __('Content', 'wp-statistics'),
          right: __('Views', 'wp-statistics'),
        },
        content:
          popularSorted.length > 0 ? (
            <div className="flex flex-col gap-3">
              {popularSorted.map((item) => renderContentItem(item))}
            </div>
          ) : (
            <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
          ),
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
          <div className="flex flex-col gap-3">
            {commentedSorted.map((item) => {
              const route = getAnalyticsRoute(item.page_type, item.page_wp_id)
              return (
                <HorizontalBar
                  key={`${item.page_uri}-${item.page_wp_id}`}
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
          </div>
        ),
      })
    }

    tabs.push({
      id: 'recent',
      label: __('Most Recent', 'wp-statistics'),
      columnHeaders: {
        left: __('Content', 'wp-statistics'),
        right: __('Views', 'wp-statistics'),
      },
      content:
        recentSorted.length > 0 ? (
          <div className="flex flex-col gap-3">
            {recentSorted.map((item) => renderContentItem(item, false))}
          </div>
        ) : (
          <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
        ),
    })

    return tabs
  }, [contentRows, isCompareEnabled, calcPercentage, comparisonDateLabel])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="px-4 py-3">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Link
              to="/categories"
              className="p-1.5 -ml-1.5 rounded-md hover:bg-neutral-100 transition-colors"
              aria-label={__('Back to Categories', 'wp-statistics')}
            >
              <ArrowLeft className="h-5 w-5 text-neutral-500" />
            </Link>
            <h1 className="text-2xl font-semibold text-neutral-800 truncate max-w-[400px]" title={categoryTitle}>
              {showSkeleton ? __('Loading...', 'wp-statistics') : categoryTitle}
            </h1>
          </div>
          <div className="flex items-center gap-3">
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
      </div>

      {/* Options Drawer */}
      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="single-category" />

        {showLockedState ? (
          <LockedState taxonomyType={taxonomyType} />
        ) : showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            {/* Metrics Skeleton */}
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={5} columns={3} />
              </PanelSkeleton>
            </div>
            {/* Chart + Traffic Summary Skeleton */}
            <div className="col-span-12 lg:col-span-8">
              <ChartSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-4">
              <PanelSkeleton showTitle={true}>
                <MetricsSkeleton count={5} columns={3} />
              </PanelSkeleton>
            </div>
            {/* Top Content Skeleton */}
            <div className="col-span-12">
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
            {/* Bar List Skeletons */}
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Metrics */}
            {isWidgetVisible('metrics') && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={categoryMetrics} columns="auto" />
                </Panel>
              </div>
            )}
            {/* Row 2: Performance Chart + Traffic Summary Sidebar */}
            {isWidgetVisible('traffic-trends') && (
              <div className="col-span-12 lg:col-span-8">
                <LineChart
                  className="h-full"
                  title={__('Performance', 'wp-statistics')}
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
            {isWidgetVisible('traffic-summary') && (
              <div className="col-span-12 lg:col-span-4">
                <SimpleTable
                  title={__('Traffic Summary', 'wp-statistics')}
                  columns={trafficSummaryColumns}
                  data={trafficSummaryData}
                  isLoading={isTrafficSummaryLoading}
                />
              </div>
            )}
            {/* Row 3: Top Content */}
            {isWidgetVisible('top-content') && (
              <div className="col-span-12">
                <TabbedPanel title={__('Top Content', 'wp-statistics')} tabs={topContentTabs} defaultTab="popular" />
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
