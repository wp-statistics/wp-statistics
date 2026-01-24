import { keepPreviousData, useQueries, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, Link } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { ArrowLeft, LockIcon } from 'lucide-react'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import {
  type OverviewOptionsConfig,
  OptionsDrawerTrigger,
  OverviewOptionsDrawer,
  OverviewOptionsProvider,
  useOverviewOptions,
} from '@/components/custom/options-drawer'
import { SimpleTable, type SimpleTableColumn } from '@/components/custom/simple-table'
import { NumericCell } from '@/components/data-table-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { type MetricConfig, type WidgetConfig } from '@/contexts/page-options-context'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { usePremiumFeature } from '@/hooks/use-premium-feature'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { getFixedDatePeriods } from '@/lib/fixed-date-ranges'
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import {
  extractTrafficSummaryData,
  getSingleContentQueryOptions,
  getTrafficSummaryPeriodQueryOptions,
} from '@/services/content-analytics/get-single-content'

// Widget configuration for Single Content Report (shared across all posts)
const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'metrics', label: __('Metrics Overview', 'wp-statistics'), defaultVisible: true },
  { id: 'traffic-summary', label: __('Traffic Summary', 'wp-statistics'), defaultVisible: true },
  { id: 'traffic-trends', label: __('Traffic Trends', 'wp-statistics'), defaultVisible: true },
  { id: 'top-referrers', label: __('Top Referrers', 'wp-statistics'), defaultVisible: true },
  { id: 'top-search-engines', label: __('Top Search Engines', 'wp-statistics'), defaultVisible: true },
  { id: 'top-countries', label: __('Top Countries', 'wp-statistics'), defaultVisible: true },
  { id: 'top-browsers', label: __('Top Browsers', 'wp-statistics'), defaultVisible: true },
  { id: 'top-operating-systems', label: __('Top Operating Systems', 'wp-statistics'), defaultVisible: true },
  { id: 'top-device-categories', label: __('Top Device Categories', 'wp-statistics'), defaultVisible: true },
]

// Metric configuration for Single Content Report
const METRIC_CONFIGS: MetricConfig[] = [
  { id: 'visitors', label: __('Visitors', 'wp-statistics'), defaultVisible: true },
  { id: 'views', label: __('Views', 'wp-statistics'), defaultVisible: true },
  { id: 'avg-time-on-page', label: __('Avg. Time on Page', 'wp-statistics'), defaultVisible: true },
  { id: 'bounce-rate', label: __('Bounce Rate', 'wp-statistics'), defaultVisible: true },
  { id: 'entry-page', label: __('Entry Page', 'wp-statistics'), defaultVisible: true },
  { id: 'exit-page', label: __('Exit Page', 'wp-statistics'), defaultVisible: true },
  { id: 'exit-rate', label: __('Exit Rate', 'wp-statistics'), defaultVisible: true },
  { id: 'comments', label: __('Comments', 'wp-statistics'), defaultVisible: true },
]

// Options configuration (shared pageId for all single content reports)
const OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'single-content',
  filterGroup: 'individual-content',
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

export const Route = createLazyFileRoute('/(content-analytics)/content_/$postId')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">{__('Error Loading Page', 'wp-statistics')}</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

/**
 * Locked state for custom post types without premium
 */
function LockedState({ postType }: { postType: string }) {
  return (
    <Panel className="p-8 text-center">
      <div className="max-w-md mx-auto space-y-4">
        <div className="w-16 h-16 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
          <LockIcon className="w-8 h-8 text-primary" strokeWidth={1.5} />
        </div>
        <h2 className="text-lg font-semibold text-neutral-800">
          {__('Custom Post Type Analytics', 'wp-statistics')}
        </h2>
        <p className="text-sm text-muted-foreground">
          {__(
            'View detailed analytics for custom post types like',
            'wp-statistics'
          )}{' '}
          <span className="font-medium">{postType}</span>.{' '}
          {__(
            'Understand how your custom content performs with visitors, views, engagement metrics, and more.',
            'wp-statistics'
          )}
        </p>
        <p className="text-sm text-muted-foreground">
          {__('This feature requires the Premium addon with Custom Post Type Support.', 'wp-statistics')}
        </p>
        <a
          href="https://wp-statistics.com/pricing/?utm_source=plugin&utm_medium=link&utm_campaign=custom-post-type-support"
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
      <SingleContentReportContent />
    </OverviewOptionsProvider>
  )
}

function SingleContentReportContent() {
  const { postId } = Route.useParams()

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

  // Page options for widget/metric visibility
  const { isWidgetVisible, isMetricVisible } = usePageOptions()

  // Options drawer (uses new reusable components)
  const options = useOverviewOptions()

  const wp = WordPress.getInstance()

  // Check if custom post type support is unlocked
  const { isEnabled: isCustomPostTypeEnabled } = usePremiumFeature('custom-post-type-support')

  // Get filter fields for individual content analytics
  // Uses 'individual-content' group which includes: Country, City, Browser, OS,
  // Device Type, Referrer, Referrer Channel, User Role, Logged In, Author
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('individual-content') as FilterField[]
  }, [wp])

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

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  // Batch query for single content data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getSingleContentQueryOptions({
      postId,
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      filters: appliedFilters || [],
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
      ...getTrafficSummaryPeriodQueryOptions({
        postId,
        period,
        filters: appliedFilters || [],
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
      const data = extractTrafficSummaryData(queryResult.data, period.id)

      return {
        label: period.label,
        visitors: data?.visitors ?? 0,
        views: data?.views ?? 0,
        previousVisitors: data?.previous?.visitors,
        previousViews: data?.previous?.views,
        comparisonLabel: period.comparisonLabel,
      }
    })
  }, [fixedDatePeriods, trafficSummaryQueries])

  // Extract data from batch response (axios returns AxiosResponse with .data property)
  const metricsResponse = batchResponse?.data?.items?.content_metrics
  const postInfoResponse = batchResponse?.data?.items?.post_info
  const trafficTrendsResponse = batchResponse?.data?.items?.traffic_trends
  const topReferrersResponse = batchResponse?.data?.items?.top_referrers
  const topSearchEnginesResponse = batchResponse?.data?.items?.top_search_engines
  const topCountriesResponse = batchResponse?.data?.items?.top_countries
  const topBrowsersResponse = batchResponse?.data?.items?.top_browsers
  const topOperatingSystemsResponse = batchResponse?.data?.items?.top_operating_systems
  const topDeviceCategoriesResponse = batchResponse?.data?.items?.top_device_categories

  // Get plugin URL for icons
  const pluginUrl = wp.getPluginUrl()

  // Transform chart data using shared hook (visitors + views only for single content)
  const { data: chartData, metrics: chartMetrics } = useChartData(trafficTrendsResponse, {
    metrics: [
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  // Get post info from table response (first row)
  const postInfoRow = postInfoResponse?.data?.rows?.[0]
  const postTitle = postInfoRow?.page_title || __('Content', 'wp-statistics')
  const postType = postInfoRow?.page_type || 'post'

  // Check if this is a custom post type (not 'post' or 'page')
  const isCustomPostType = postType !== 'post' && postType !== 'page'

  // Show locked state for custom post types without premium
  const showLockedState = isCustomPostType && !isCustomPostTypeEnabled

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Only show skeleton on initial load (no data yet), not on refetches
  const showSkeleton = isLoading && !batchResponse
  // Show full page loading when filters/dates change (not timeframe-only)
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  // Show loading indicator on chart only when timeframe changes
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Build metrics with visibility filtering
  const contentMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const visitors = getTotalValue(totals.visitors)
    const views = getTotalValue(totals.views)
    const bounceRate = getTotalValue(totals.bounce_rate)
    const avgTimeOnPage = getTotalValue(totals.avg_time_on_page)
    const entryPage = getTotalValue(totals.entry_page)
    const exitPage = getTotalValue(totals.exit_page)
    const exitRate = getTotalValue(totals.exit_rate)
    const comments = getTotalValue(totals.comments)

    const prevVisitors = getTotalValue(totals.visitors?.previous)
    const prevViews = getTotalValue(totals.views?.previous)
    const prevBounceRate = getTotalValue(totals.bounce_rate?.previous)
    const prevAvgTimeOnPage = getTotalValue(totals.avg_time_on_page?.previous)
    const prevEntryPage = getTotalValue(totals.entry_page?.previous)
    const prevExitPage = getTotalValue(totals.exit_page?.previous)
    const prevExitRate = getTotalValue(totals.exit_rate?.previous)
    const prevComments = getTotalValue(totals.comments?.previous)

    // Build all metrics with IDs for filtering
    const allMetrics = [
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
        id: 'entry-page',
        label: __('Entry Page', 'wp-statistics'),
        value: formatCompactNumber(entryPage),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(entryPage, prevEntryPage),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevEntryPage),
            }
          : {}),
      },
      {
        id: 'exit-page',
        label: __('Exit Page', 'wp-statistics'),
        value: formatCompactNumber(exitPage),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(exitPage, prevExitPage),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevExitPage),
            }
          : {}),
      },
      {
        id: 'exit-rate',
        label: __('Exit Rate', 'wp-statistics'),
        value: `${formatDecimal(exitRate)}%`,
        ...(isCompareEnabled
          ? {
              ...calcPercentage(exitRate, prevExitRate),
              comparisonDateLabel,
              previousValue: `${formatDecimal(prevExitRate)}%`,
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
    ]

    // Filter metrics based on visibility
    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [metricsResponse, calcPercentage, isCompareEnabled, comparisonDateLabel, isMetricVisible])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3">
        <div className="flex items-center gap-3">
          <Link
            to="/content"
            className="p-1.5 -ml-1.5 rounded-md hover:bg-neutral-100 transition-colors"
            aria-label={__('Back to Content', 'wp-statistics')}
          >
            <ArrowLeft className="h-5 w-5 text-neutral-500" />
          </Link>
          <h1 className="text-2xl font-semibold text-neutral-800 truncate max-w-[400px]" title={postTitle}>
            {showSkeleton ? __('Loading...', 'wp-statistics') : postTitle}
          </h1>
        </div>
        <div className="flex items-center gap-3">
          <div className="hidden lg:flex">
            {filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={appliedFilters || []}
                onApplyFilters={handleApplyFilters}
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
      <OverviewOptionsDrawer
        config={OPTIONS_CONFIG}
        isOpen={options.isOpen}
        setIsOpen={options.setIsOpen}
        resetToDefaults={options.resetToDefaults}
      />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="single-content" />

        {showLockedState ? (
          <LockedState postType={postType} />
        ) : showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            {/* Metrics Skeleton */}
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={8} columns={4} />
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
            {isWidgetVisible('metrics') && contentMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={contentMetrics} columns={4} />
                </Panel>
              </div>
            )}
            {/* Row 2: Traffic Trends Chart + Traffic Summary Sidebar */}
            {isWidgetVisible('traffic-trends') && (
              <div className="col-span-12 lg:col-span-8">
                <LineChart
                  className="h-full"
                  title={__('Traffic Trends', 'wp-statistics')}
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
            {/* Row 3: Top Referrers & Top Search Engines */}
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
            {/* Row 4: Top Countries & Top Browsers */}
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
            {/* Row 5: Top Operating Systems & Top Device Categories */}
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
