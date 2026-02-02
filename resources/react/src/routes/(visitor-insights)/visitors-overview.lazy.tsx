import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { GlobalMap } from '@/components/custom/global-map'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import {
  type OverviewOptionsConfig,
  OverviewOptionsDrawer,
  OverviewOptionsProvider,
  useOverviewOptions,
} from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import {
  BarListSkeleton,
  ChartSkeleton,
  MetricsSkeleton,
  PanelSkeleton,
  TableSkeleton,
} from '@/components/ui/skeletons'
import { pickMetrics } from '@/constants/metric-definitions'
import { type WidgetConfig } from '@/contexts/page-options-context'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { calcSharePercentage, decodeText, formatCompactNumber, formatDecimal, formatDuration, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getVisitorOverviewQueryOptions } from '@/services/visitor-insight/get-visitor-overview'

import { OverviewTopVisitors } from './-components/overview/overview-top-visitors'

// Widget configuration for this page
const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'metrics', label: __('Metrics Overview', 'wp-statistics'), defaultVisible: true },
  { id: 'traffic-trends', label: __('Traffic Trends', 'wp-statistics'), defaultVisible: true },
  { id: 'top-referrers', label: __('Top Referrers', 'wp-statistics'), defaultVisible: true },
  { id: 'top-countries', label: __('Top Countries', 'wp-statistics'), defaultVisible: true },
  { id: 'device-type', label: __('Device Type', 'wp-statistics'), defaultVisible: true },
  { id: 'operating-systems', label: __('Operating Systems', 'wp-statistics'), defaultVisible: true },
  { id: 'top-visitors', label: __('Top Visitors', 'wp-statistics'), defaultVisible: true },
  { id: 'global-map', label: __('Global Visitor Distribution', 'wp-statistics'), defaultVisible: true },
]

// Metric configuration for this page
const METRIC_CONFIGS = pickMetrics('visitors', 'views', 'sessionDuration', 'viewsPerSession', 'topCountry', 'topReferrer', 'topSearchTerm', 'loggedInShare')

// Options configuration for this page
const OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'visitors-overview',
  filterGroup: 'visitors',
  widgetConfigs: WIDGET_CONFIGS,
  metricConfigs: METRIC_CONFIGS,
}

export const Route = createLazyFileRoute('/(visitor-insights)/visitors-overview')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">Error Loading Page</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  return (
    <OverviewOptionsProvider config={OPTIONS_CONFIG}>
      <VisitorsOverviewContent />
    </OverviewOptionsProvider>
  )
}

function VisitorsOverviewContent() {
  // Use global filters context for date range and filters (hybrid URL + preferences)
  const {
    dateFrom,
    dateTo,
    filters: appliedFilters,
    isInitialized,
    isCompareEnabled,
    apiDateParams,
  } = useGlobalFilters()

  const navigate = useNavigate()

  // Page options for widget/metric visibility
  const { isWidgetVisible, isMetricVisible } = usePageOptions()

  // Options drawer - config is passed once and returned for drawer
  const options = useOverviewOptions(OPTIONS_CONFIG)

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Track if only timeframe changed (for loading behavior)
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevFiltersRef = useRef<string>(JSON.stringify(appliedFilters))
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  // Detect what changed when data is being fetched
  useEffect(() => {
    const currentFilters = JSON.stringify(appliedFilters)

    const filtersChanged = currentFilters !== prevFiltersRef.current
    const dateRangeChanged = dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current

    // If filters or dates changed, it's NOT a timeframe-only change
    if (filtersChanged || dateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    // Update refs
    prevFiltersRef.current = currentFilters
    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [appliedFilters, dateFrom, dateTo])

  // Custom timeframe setter that tracks the change type
  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  // Batch query for all overview data (only when filters are initialized)
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getVisitorOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
      filters: appliedFilters || [],
    }),
    retry: false,
    placeholderData: keepPreviousData, // Keep showing old data while fetching new data
    enabled: isInitialized,
  })

  // Only show skeleton on initial load (no data yet), not on refetches
  const showSkeleton = isLoading && !batchResponse
  // Show full page loading when filters/dates change (not timeframe-only)
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  // Show loading indicator on chart only when timeframe changes
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Extract data from batch response
  // Batch API returns: { success, items: { metrics, traffic_trends, ... } }
  // axios wraps in { data: responseBody }, so batchResponse.data = API response
  // Metrics uses flat format - totals at top level
  const metricsResponse = batchResponse?.data?.items?.metrics
  // Context metrics for second row (top country, referrer, search, logged-in)
  const metricsTopCountry = batchResponse?.data?.items?.metrics_top_country
  const metricsTopReferrer = batchResponse?.data?.items?.metrics_top_referrer
  const metricsTopSearch = batchResponse?.data?.items?.metrics_top_search
  const metricsLoggedIn = batchResponse?.data?.items?.metrics_logged_in
  // Traffic trends uses chart format - labels and datasets at top level
  const trafficTrendsResponse = batchResponse?.data?.items?.traffic_trends
  // Table format queries - each has data.rows and data.totals structure
  const topCountriesData = batchResponse?.data?.items?.top_countries?.data?.rows || []
  const topCountriesTotals = batchResponse?.data?.items?.top_countries?.data?.totals
  const deviceTypeData = batchResponse?.data?.items?.device_type?.data?.rows || []
  const deviceTypeTotals = batchResponse?.data?.items?.device_type?.data?.totals
  const operatingSystemsData = batchResponse?.data?.items?.operating_systems?.data?.rows || []
  const operatingSystemsTotals = batchResponse?.data?.items?.operating_systems?.data?.totals
  const topReferrersData = batchResponse?.data?.items?.top_referrers?.data?.rows || []
  const topReferrersTotals = batchResponse?.data?.items?.top_referrers?.data?.totals
  const countriesMapData = batchResponse?.data?.items?.countries_map?.data?.rows || []

  // Transform chart data using shared hook
  const { data: chartData, metrics: trafficTrendsMetrics } = useChartData(trafficTrendsResponse, {
    metrics: [
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  // Transform countries map data for GlobalMap component
  const globalMapData = useMemo(
    () => ({
      countries: countriesMapData
        .filter((item) => item.country_code && item.country_name)
        .map((item) => ({
          code: item.country_code.toLowerCase(),
          name: item.country_name,
          visitors: Number(item.visitors) || 0,
          views: Number(item.views) || 0,
        })),
    }),
    [countriesMapData]
  )

  // Use the shared percentage calculation hook
  const calcPercentage = usePercentageCalc()
  // Get comparison date label for tooltips
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Build metrics from batch response (flat format - totals at top level)
  // Layout: 4 columns, 2 rows
  // Row 1 (with comparison): Visitors, Views, Session Duration, Views Per Session
  // Row 2 (context): Top Country, Top Referrer, Top Search Term, Logged-in Share
  const overviewMetrics = useMemo(() => {
    const totals = metricsResponse?.totals

    if (!totals) return []

    // Extract current and previous values from { current, previous } structure
    const visitors = getTotalValue(totals.visitors)
    const views = getTotalValue(totals.views)
    const avgSessionDuration = getTotalValue(totals.avg_session_duration)
    const pagesPerSession = getTotalValue(totals.pages_per_session)

    const prevVisitors = getTotalValue(totals.visitors?.previous)
    const prevViews = getTotalValue(totals.views?.previous)
    const prevAvgSessionDuration = getTotalValue(totals.avg_session_duration?.previous)
    const prevPagesPerSession = getTotalValue(totals.pages_per_session?.previous)

    // Context metrics (second row)
    const topCountryName = metricsTopCountry?.items?.[0]?.country_name
    const topReferrerName = metricsTopReferrer?.items?.[0]?.referrer_name
    const topSearchTerm = decodeText(metricsTopSearch?.items?.[0]?.search_term)

    // Calculate logged-in share percentage (capped at 100% to handle data inconsistencies)
    const loggedInVisitors = Number(metricsLoggedIn?.totals?.visitors?.current) || 0
    const prevLoggedInVisitors = Number(metricsLoggedIn?.totals?.visitors?.previous) || 0
    const loggedInShare = calcSharePercentage(loggedInVisitors, visitors)
    const prevLoggedInShare = calcSharePercentage(prevLoggedInVisitors, prevVisitors)

    // Build all metrics with IDs for filtering
    const allMetrics = [
      // Row 1: Numeric metrics with comparison
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
        id: 'session-duration',
        label: __('Session Duration', 'wp-statistics'),
        value: formatDuration(avgSessionDuration),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(avgSessionDuration, prevAvgSessionDuration),
              comparisonDateLabel,
              previousValue: formatDuration(prevAvgSessionDuration),
            }
          : {}),
      },
      {
        id: 'views-per-session',
        label: __('Views/Session', 'wp-statistics'),
        value: formatDecimal(pagesPerSession),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(pagesPerSession, prevPagesPerSession),
              comparisonDateLabel,
              previousValue: formatDecimal(prevPagesPerSession),
            }
          : {}),
      },
      // Row 2: Context metrics (strings use '-' when empty)
      {
        id: 'top-country',
        label: __('Top Country', 'wp-statistics'),
        value: topCountryName || '-',
      },
      {
        id: 'top-referrer',
        label: __('Top Referrer', 'wp-statistics'),
        value: topReferrerName || '-',
      },
      {
        id: 'top-search-term',
        label: __('Top Search Term', 'wp-statistics'),
        value: topSearchTerm || '-',
      },
      {
        id: 'logged-in-share',
        label: __('Logged-in Share', 'wp-statistics'),
        value: `${formatDecimal(loggedInShare)}%`,
        ...(isCompareEnabled
          ? {
              ...calcPercentage(loggedInShare, prevLoggedInShare),
              comparisonDateLabel,
              previousValue: `${formatDecimal(prevLoggedInShare)}%`,
            }
          : {}),
      },
    ]

    // Filter metrics based on visibility
    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [metricsResponse, metricsTopCountry, metricsTopReferrer, metricsTopSearch, metricsLoggedIn, isCompareEnabled, comparisonDateLabel, isMetricVisible])

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Visitors Overview', 'wp-statistics')}
        filterGroup="visitors"
        optionsTriggerProps={options.triggerProps}
      />

      {/* Options Drawer */}
      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-3" currentRoute="visitors-overview" />

        {/* Applied filters row (separate from button) */}

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            {/* Metrics skeleton */}
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={8} columns={4} />
              </PanelSkeleton>
            </div>
            {/* Chart skeleton */}
            <div className="col-span-12">
              <PanelSkeleton titleWidth="w-32">
                <ChartSkeleton height={256} showTitle={false} />
              </PanelSkeleton>
            </div>
            {/* Top Referrers skeleton */}
            <div className="col-span-12">
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
            {/* Three column lists skeleton (Countries, Devices, OS) */}
            {[1, 2, 3].map((i) => (
              <div key={i} className="col-span-4">
                <PanelSkeleton>
                  <BarListSkeleton items={5} showIcon />
                </PanelSkeleton>
              </div>
            ))}
            {/* Top Visitors skeleton */}
            <div className="col-span-12">
              <PanelSkeleton>
                <TableSkeleton rows={5} columns={4} />
              </PanelSkeleton>
            </div>
            {/* Global Map skeleton */}
            <div className="col-span-12">
              <PanelSkeleton titleWidth="w-40">
                <ChartSkeleton height={256} showTitle={false} />
              </PanelSkeleton>
            </div>
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {isWidgetVisible('metrics') && overviewMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={overviewMetrics} />
                </Panel>
              </div>
            )}

            {isWidgetVisible('traffic-trends') && (
              <div className="col-span-12">
                <LineChart
                  title="Traffic Trends"
                  data={chartData}
                  metrics={trafficTrendsMetrics}
                  showPreviousPeriod={isCompareEnabled}
                  timeframe={timeframe}
                  onTimeframeChange={handleTimeframeChange}
                  loading={isChartRefetching}
                  compareDateTo={apiDateParams.previous_date_to}
                  dateTo={apiDateParams.date_to}
                />
              </div>
            )}

            {isWidgetVisible('top-referrers') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Referrers', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Referrer', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topReferrersData, {
                    label: (item) =>
                      item.referrer_name || item.referrer_domain || item.referrer_channel || __('Direct', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topReferrersTotals?.visitors?.current ?? topReferrersTotals?.visitors) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                  link={{
                    action: () => navigate({ to: '/referrers' }),
                  }}
                />
              </div>
            )}

            {isWidgetVisible('top-countries') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Countries', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Country', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topCountriesData, {
                    label: (item) => item.country_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topCountriesTotals?.visitors?.current ?? topCountriesTotals?.visitors) || 1,
                    icon: (item) => (
                      <img
                        src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
                        alt={item.country_name || ''}
                        className="w-4 h-3"
                      />
                    ),
                    isCompareEnabled,
                    comparisonDateLabel,
                    linkTo: () => '/country/$countryCode',
                    linkParams: (item) => ({ countryCode: item.country_code?.toLowerCase() || '000' }),
                  })}
                  link={{
                    action: () => navigate({ to: '/countries' }),
                  }}
                />
              </div>
            )}

            {isWidgetVisible('device-type') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Device Type', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Device', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(deviceTypeData, {
                    label: (item) => item.device_type_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(deviceTypeTotals?.visitors?.current ?? deviceTypeTotals?.visitors) || 1,
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
                  link={{
                    action: () => navigate({ to: '/browsers' }),
                  }}
                />
              </div>
            )}

            {isWidgetVisible('operating-systems') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Operating Systems', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('OS', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(operatingSystemsData, {
                    label: (item) => item.os_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(operatingSystemsTotals?.visitors?.current ?? operatingSystemsTotals?.visitors) || 1,
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
                  link={{
                    action: () => navigate({ to: '/operating-systems' }),
                  }}
                />
              </div>
            )}

            {isWidgetVisible('top-visitors') && (
              <div className="col-span-12">
                <OverviewTopVisitors data={batchResponse?.data?.items?.top_visitors?.data?.rows} isFetching={isFetching} />
              </div>
            )}

            {isWidgetVisible('global-map') && (
              <div className="col-span-12">
                <GlobalMap
                  data={globalMapData}
                  isLoading={isLoading}
                  dateFrom={apiDateParams.date_from}
                  dateTo={apiDateParams.date_to}
                  metric="Visitors"
                  showZoomControls={true}
                  showLegend={true}
                  pluginUrl={pluginUrl}
                  title={__('Global Visitor Distribution', 'wp-statistics')}
                  enableCityDrilldown={true}
                  enableMetricToggle={true}
                  availableMetrics={[
                    { value: 'visitors', label: 'Visitors' },
                    { value: 'views', label: 'Views' },
                  ]}
                />
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  )
}
