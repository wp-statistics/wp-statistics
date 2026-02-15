import { keepPreviousData, useQueries, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { ExternalLink } from 'lucide-react'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { BackButton } from '@/components/custom/back-button'
import { DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import {
  OptionsDrawerTrigger,
  type OverviewOptionsConfig,
  OverviewOptionsDrawer,
  OverviewOptionsProvider,
  useOverviewOptions,
} from '@/components/custom/options-drawer'
import { SimpleTable, type SimpleTableColumn } from '@/components/custom/simple-table'
import { NumericCell } from '@/components/data-table-columns'
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
import { transformToBarList } from '@/lib/bar-list-helpers'
import { getFixedDatePeriods } from '@/lib/fixed-date-ranges'
import { NON_LINKABLE_TYPES } from '@/lib/url-utils'
import { formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import {
  extractUrlTrafficSummaryData,
  getSingleUrlQueryOptions,
  getUrlTrafficSummaryPeriodQueryOptions,
} from '@/services/page-insight/get-single-url'

/**
 * Human-readable labels for page types
 */
const PAGE_TYPE_LABELS: Record<string, string> = {
  home: __('Home Page', 'wp-statistics'),
  search: __('Search Results', 'wp-statistics'),
  '404': __('404 Page', 'wp-statistics'),
  archive: __('Archive', 'wp-statistics'),
  date_archive: __('Date Archive', 'wp-statistics'),
  post_type_archive: __('Post Type Archive', 'wp-statistics'),
  feed: __('Feed', 'wp-statistics'),
  loginpage: __('Login Page', 'wp-statistics'),
  author_archive: __('Author Archive', 'wp-statistics'),
  category: __('Category Archive', 'wp-statistics'),
  post_tag: __('Tag Archive', 'wp-statistics'),
}

/** Page types for archive pages that have a page_wp_id but should NOT redirect to /content */
const ARCHIVE_PAGE_TYPES = new Set(['category', 'post_tag'])

// Widget configuration for Single URL Report
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

// Metric configuration (no comments)
const METRIC_CONFIGS = pickMetrics('visitors', 'views', 'avgTimeOnPage', 'bounceRate', 'entryPage', 'exitPage', 'exitRate')

// Options configuration
const OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'single-url',
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

export const Route = createLazyFileRoute('/(page-insights)/url_/$resourceId')({
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
      <SingleUrlReportContent />
    </OverviewOptionsProvider>
  )
}

function SingleUrlReportContent() {
  const { resourceId } = Route.useParams()
  const navigate = useNavigate()

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

  const { isWidgetVisible, isMetricVisible } = usePageOptions()
  const options = useOverviewOptions(OPTIONS_CONFIG)

  const wp = WordPress.getInstance()

  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('individual-content') as FilterField[]
  }, [wp])

  // Chart timeframe state
  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevFiltersRef = useRef<string>(JSON.stringify(appliedFilters))
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  useEffect(() => {
    const filtersChanged = JSON.stringify(appliedFilters) !== prevFiltersRef.current
    const dateRangeChanged = dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current

    if (filtersChanged || dateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    prevFiltersRef.current = JSON.stringify(appliedFilters)
    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [appliedFilters, dateFrom, dateTo])

  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  // Batch query for single URL data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getSingleUrlQueryOptions({
      resourceId,
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

  // Fixed date periods for traffic summary
  const fixedDatePeriods = useMemo(() => getFixedDatePeriods(), [])

  // Parallel queries for traffic summary data
  const trafficSummaryQueries = useQueries({
    queries: fixedDatePeriods.map((p) => ({
      ...getUrlTrafficSummaryPeriodQueryOptions({
        resourceId,
        period: p,
        filters: appliedFilters || [],
      }),
      enabled: isInitialized,
      placeholderData: keepPreviousData,
    })),
  })

  const isTrafficSummaryLoading = trafficSummaryQueries.some((q) => q.isLoading)

  const trafficSummaryData = useMemo<TrafficSummaryRow[]>(() => {
    return fixedDatePeriods.map((p, index) => {
      const queryResult = trafficSummaryQueries[index]
      const data = extractUrlTrafficSummaryData(queryResult.data, p.id)

      return {
        label: p.label,
        visitors: data?.visitors ?? 0,
        views: data?.views ?? 0,
        previousVisitors: data?.previous?.visitors,
        previousViews: data?.previous?.views,
        comparisonLabel: p.comparisonLabel,
      }
    })
  // eslint-disable-next-line @tanstack/query/no-unstable-deps -- Intentionally using entire useQueries result for transformation
  }, [fixedDatePeriods, trafficSummaryQueries])

  // Extract data from batch response
  const metricsResponse = batchResponse?.data?.items?.url_metrics
  const urlInfoResponse = batchResponse?.data?.items?.url_info
  const trafficTrendsResponse = batchResponse?.data?.items?.traffic_trends
  const topReferrersResponse = batchResponse?.data?.items?.top_referrers
  const topSearchEnginesResponse = batchResponse?.data?.items?.top_search_engines
  const topCountriesResponse = batchResponse?.data?.items?.top_countries
  const topBrowsersResponse = batchResponse?.data?.items?.top_browsers
  const topOperatingSystemsResponse = batchResponse?.data?.items?.top_operating_systems
  const topDeviceCategoriesResponse = batchResponse?.data?.items?.top_device_categories

  const pluginUrl = wp.getPluginUrl()

  // Chart data
  const { data: chartData, metrics: chartMetrics } = useChartData(trafficTrendsResponse, {
    metrics: [
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  // Get URL info from table response
  const urlInfoRow = urlInfoResponse?.data?.rows?.[0]
  const pageUri = urlInfoRow?.page_uri || '/'
  const pageTitle = urlInfoRow?.page_title || pageUri
  const pageType = urlInfoRow?.page_type || 'unknown'
  const permalink = urlInfoRow?.permalink
  const pageWpId = urlInfoRow?.page_wp_id

  // Access guard: redirect linkable content types to their proper report
  useEffect(() => {
    if (!urlInfoRow) return

    // If this is a regular content type with a WP ID, redirect to content report.
    // Skip author and taxonomy archive pages — they are archive URLs, not individual content.
    if (pageWpId && !NON_LINKABLE_TYPES.has(pageType) && !ARCHIVE_PAGE_TYPES.has(pageType) && pageType !== 'unknown') {
      navigate({ to: '/content/$postId', params: { postId: String(pageWpId) }, replace: true })
    }
  }, [urlInfoRow, pageType, pageWpId, navigate])

  const pageTypeLabel = PAGE_TYPE_LABELS[pageType] || pageType

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  const showSkeleton = isLoading && !batchResponse
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Build metrics (7 metrics, no comments)
  const urlMetrics = useMemo(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    const visitors = getTotalValue(totals.visitors)
    const views = getTotalValue(totals.views)
    const bounceRate = getTotalValue(totals.bounce_rate)
    const avgTimeOnPage = getTotalValue(totals.avg_time_on_page)
    const entryPage = getTotalValue(totals.entry_page)
    const exitPage = getTotalValue(totals.exit_page)
    const exitRate = getTotalValue(totals.exit_rate)

    const prevVisitors = getTotalValue(totals.visitors?.previous)
    const prevViews = getTotalValue(totals.views?.previous)
    const prevBounceRate = getTotalValue(totals.bounce_rate?.previous)
    const prevAvgTimeOnPage = getTotalValue(totals.avg_time_on_page?.previous)
    const prevEntryPage = getTotalValue(totals.entry_page?.previous)
    const prevExitPage = getTotalValue(totals.exit_page?.previous)
    const prevExitRate = getTotalValue(totals.exit_rate?.previous)

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
    ]

    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [metricsResponse, calcPercentage, isCompareEnabled, comparisonDateLabel, isMetricVisible])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="px-4 py-3">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3 min-w-0">
            <BackButton defaultTo="/top-pages" label={__('Back to Top Pages', 'wp-statistics')} />
            <h1 className="text-2xl font-semibold text-neutral-800 truncate max-w-[400px]" title={pageTitle}>
              {showSkeleton ? __('Loading...', 'wp-statistics') : pageTitle}
            </h1>
            {!showSkeleton && (
              <span className="inline-flex items-center rounded-md bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600 shrink-0">
                {pageTypeLabel}
              </span>
            )}
            {!showSkeleton && permalink && (
              <a
                href={permalink}
                target="_blank"
                rel="noopener noreferrer"
                className="p-1 rounded-md hover:bg-neutral-100 transition-colors shrink-0"
                aria-label={__('Open page', 'wp-statistics')}
              >
                <ExternalLink className="h-4 w-4 text-neutral-400" />
              </a>
            )}
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
      </div>

      {/* Options Drawer */}
      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="single-url" />

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={7} columns={4} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-8">
              <ChartSkeleton />
            </div>
            <div className="col-span-12 lg:col-span-4">
              <PanelSkeleton showTitle={true}>
                <MetricsSkeleton count={5} columns={3} />
              </PanelSkeleton>
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
            <div className="col-span-12 lg:col-span-6">
              <BarListSkeleton />
            </div>
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Metrics */}
            {isWidgetVisible('metrics') && urlMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={urlMetrics} columns="auto" />
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
