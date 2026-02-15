import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

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
import { getChannelDisplayName } from '@/components/data-table-columns/source-categories-columns'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import {
  BarListSkeleton,
  ChartSkeleton,
  MetricsSkeleton,
  PanelSkeleton,
} from '@/components/ui/skeletons'
import { pickMetrics } from '@/constants/metric-definitions'
import { type WidgetConfig } from '@/contexts/page-options-context'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { formatCompactNumber, getTotalValue } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getReferralsOverviewQueryOptions } from '@/services/referrals/get-referrals-overview'

// Widget configuration for this page
const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'metrics', label: __('Metrics Overview', 'wp-statistics'), defaultVisible: true },
  { id: 'traffic-trends', label: __('Traffic Trends', 'wp-statistics'), defaultVisible: true },
  { id: 'top-referrers', label: __('Top Referrers', 'wp-statistics'), defaultVisible: true },
  { id: 'top-source-categories', label: __('Top Source Categories', 'wp-statistics'), defaultVisible: true },
  { id: 'top-search-engines', label: __('Top Search Engines', 'wp-statistics'), defaultVisible: true },
  { id: 'top-social-media', label: __('Top Social Media', 'wp-statistics'), defaultVisible: true },
  { id: 'top-countries', label: __('Top Countries', 'wp-statistics'), defaultVisible: true },
  { id: 'top-operating-systems', label: __('Top Operating Systems', 'wp-statistics'), defaultVisible: true },
  { id: 'top-device-categories', label: __('Top Device Categories', 'wp-statistics'), defaultVisible: true },
]

// Metric configuration for this page
const METRIC_CONFIGS = pickMetrics(
  'referredVisitors',
  'topReferrer',
  'topSearchEngine',
  'topSocialMedia',
  'topEntryPage'
)

// Options configuration for this page
const OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'referrals-overview',
  filterGroup: 'referrals',
  widgetConfigs: WIDGET_CONFIGS,
  metricConfigs: METRIC_CONFIGS,
}

export const Route = createLazyFileRoute('/(referrals)/referrals-overview')({
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
      <ReferralsOverviewContent />
    </OverviewOptionsProvider>
  )
}

function ReferralsOverviewContent() {
  // Get WordPress instance for plugin URL (icons)
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  // Use global filters context for date range and filters (hybrid URL + preferences)
  const {
    dateFrom,
    dateTo,
    filters: appliedFilters,
    isInitialized,
    isCompareEnabled,
    apiDateParams,
  } = useGlobalFilters()

  // Page options for widget/metric visibility
  const { isWidgetVisible, isMetricVisible } = usePageOptions()

  // Options drawer - config is passed once and returned for drawer
  const options = useOverviewOptions(OPTIONS_CONFIG)

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
    ...getReferralsOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
      filters: appliedFilters || [],
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
  const metricsResponse = batchResponse?.data?.items?.metrics
  const metricsTopReferrer = batchResponse?.data?.items?.metrics_top_referrer
  const metricsTopSearchEngine = batchResponse?.data?.items?.metrics_top_search_engine
  const metricsTopSocial = batchResponse?.data?.items?.metrics_top_social
  const metricsTopEntryPage = batchResponse?.data?.items?.metrics_top_entry_page
  const trafficTrendsResponse = batchResponse?.data?.items?.traffic_trends
  const topReferrersData = batchResponse?.data?.items?.top_referrers?.data?.rows || []
  const topReferrersTotals = batchResponse?.data?.items?.top_referrers?.data?.totals
  const topSourceCategoriesData = batchResponse?.data?.items?.top_source_categories?.data?.rows || []
  const topSourceCategoriesTotals = batchResponse?.data?.items?.top_source_categories?.data?.totals
  const topSearchEnginesData = batchResponse?.data?.items?.top_search_engines?.data?.rows || []
  const topSearchEnginesTotals = batchResponse?.data?.items?.top_search_engines?.data?.totals
  const topSocialMediaData = batchResponse?.data?.items?.top_social_media?.data?.rows || []
  const topSocialMediaTotals = batchResponse?.data?.items?.top_social_media?.data?.totals
  const topCountriesData = batchResponse?.data?.items?.top_countries?.data?.rows || []
  const topCountriesTotals = batchResponse?.data?.items?.top_countries?.data?.totals
  const topOperatingSystemsData = batchResponse?.data?.items?.top_operating_systems?.data?.rows || []
  const topOperatingSystemsTotals = batchResponse?.data?.items?.top_operating_systems?.data?.totals
  const topDeviceCategoriesData = batchResponse?.data?.items?.top_device_categories?.data?.rows || []
  const topDeviceCategoriesTotals = batchResponse?.data?.items?.top_device_categories?.data?.totals

  // Transform chart data using shared hook
  const { data: chartData, metrics: trafficTrendsMetrics } = useChartData(trafficTrendsResponse, {
    metrics: [
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  // Use the shared percentage calculation hook
  const calcPercentage = usePercentageCalc()
  // Get comparison date label for tooltips
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Build metrics from batch response
  const overviewMetrics = useMemo(() => {
    const totals = metricsResponse?.totals

    // Extract current and previous values
    const referredVisitors = getTotalValue(totals?.visitors)
    const prevReferredVisitors = getTotalValue(totals?.visitors?.previous)

    // Context metrics (text values)
    const topReferrerName = metricsTopReferrer?.items?.[0]?.referrer_name || metricsTopReferrer?.items?.[0]?.referrer_domain
    const topSearchEngineName = metricsTopSearchEngine?.items?.[0]?.referrer_name
    const topSocialMediaName = metricsTopSocial?.items?.[0]?.referrer_name
    const topEntryPageTitle = metricsTopEntryPage?.items?.[0]?.page_title

    // Build all metrics with IDs for filtering
    const allMetrics = [
      // Referred Visitors (numeric with comparison)
      {
        id: 'referred-visitors',
        label: __('Referred Visitors', 'wp-statistics'),
        value: formatCompactNumber(referredVisitors),
        ...(isCompareEnabled
          ? {
              ...calcPercentage(referredVisitors, prevReferredVisitors),
              comparisonDateLabel,
              previousValue: formatCompactNumber(prevReferredVisitors),
            }
          : {}),
      },
      // Top Referrer (text value)
      {
        id: 'top-referrer',
        label: __('Top Referrer', 'wp-statistics'),
        value: topReferrerName || '-',
      },
      // Top Search Engine (text value)
      {
        id: 'top-search-engine',
        label: __('Top Search Engine', 'wp-statistics'),
        value: topSearchEngineName || '-',
      },
      // Top Social Media (text value)
      {
        id: 'top-social-media',
        label: __('Top Social Media', 'wp-statistics'),
        value: topSocialMediaName || '-',
      },
      // Top Entry Page (text value)
      {
        id: 'top-entry-page',
        label: __('Top Entry Page', 'wp-statistics'),
        value: topEntryPageTitle || '-',
      },
    ]

    // Filter metrics based on visibility
    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [
    metricsResponse,
    metricsTopReferrer,
    metricsTopSearchEngine,
    metricsTopSocial,
    metricsTopEntryPage,
    isCompareEnabled,
    comparisonDateLabel,
    isMetricVisible,
    calcPercentage,
  ])

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Referrals Overview', 'wp-statistics')}
        filterGroup="referrals"
        optionsTriggerProps={options.triggerProps}
      />

      {/* Options Drawer */}
      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-3" currentRoute="referrals-overview" />

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            {/* Metrics skeleton */}
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={5} columns={5} />
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
            {/* Row 4: Top Source Categories, Top Search Engines skeleton */}
            <div className="col-span-12 lg:col-span-6">
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-6">
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
            {/* Row 5: Top Social Media, Top Countries skeleton */}
            <div className="col-span-12 lg:col-span-6">
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-6">
              <PanelSkeleton>
                <BarListSkeleton items={5} showIcon />
              </PanelSkeleton>
            </div>
            {/* Row 6: Top Operating Systems, Top Device Categories skeleton */}
            <div className="col-span-12 lg:col-span-6">
              <PanelSkeleton>
                <BarListSkeleton items={5} showIcon />
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-6">
              <PanelSkeleton>
                <BarListSkeleton items={5} showIcon />
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
                  title={__('Traffic Trends', 'wp-statistics')}
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
              <div className="col-span-12">
                <HorizontalBarList
                  title={__('Top Referrers', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Referrer', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topReferrersData, {
                    label: (item) =>
                      item.referrer_name || item.referrer_domain || item.referrer_channel || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topReferrersTotals?.visitors?.current ?? topReferrersTotals?.visitors) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                />
              </div>
            )}

            {/* Row 4: Top Source Categories, Top Search Engines */}
            {isWidgetVisible('top-source-categories') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Source Categories', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Category', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topSourceCategoriesData, {
                    label: (item) => getChannelDisplayName(item.referrer_channel || 'unassigned'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topSourceCategoriesTotals?.visitors?.current ?? topSourceCategoriesTotals?.visitors) || 1,
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
                  items={transformToBarList(topSearchEnginesData, {
                    label: (item) => item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topSearchEnginesTotals?.visitors?.current ?? topSearchEnginesTotals?.visitors) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                />
              </div>
            )}

            {/* Row 5: Top Social Media, Top Countries */}
            {isWidgetVisible('top-social-media') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Social Media', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Social Network', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topSocialMediaData, {
                    label: (item) => item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topSocialMediaTotals?.visitors?.current ?? topSocialMediaTotals?.visitors) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
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
                />
              </div>
            )}

            {/* Row 6: Top Operating Systems, Top Device Categories */}
            {isWidgetVisible('top-operating-systems') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Operating Systems', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('OS', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topOperatingSystemsData, {
                    label: (item) => item.os_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topOperatingSystemsTotals?.visitors?.current ?? topOperatingSystemsTotals?.visitors) || 1,
                    icon: (item) => (
                      <img
                        src={`${pluginUrl}public/images/operating-system/${(item.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
                        alt={item.os_name || ''}
                        className="w-4 h-4"
                      />
                    ),
                    isCompareEnabled,
                    comparisonDateLabel,
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
                  items={transformToBarList(topDeviceCategoriesData, {
                    label: (item) => item.device_type_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topDeviceCategoriesTotals?.visitors?.current ?? topDeviceCategoriesTotals?.visitors) || 1,
                    icon: (item) => (
                      <img
                        src={`${pluginUrl}public/images/device/${(item.device_type_name || 'desktop').toLowerCase()}.svg`}
                        alt={item.device_type_name || ''}
                        className="w-4 h-4"
                      />
                    ),
                    isCompareEnabled,
                    comparisonDateLabel,
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
