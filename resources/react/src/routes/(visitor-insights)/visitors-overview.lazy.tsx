import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { GlobalMap } from '@/components/custom/global-map'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import { Panel } from '@/components/ui/panel'
import { NoticeContainer } from '@/components/ui/notice-container'
import {
  BarListSkeleton,
  ChartSkeleton,
  MetricsSkeleton,
  PanelSkeleton,
  TableSkeleton,
} from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { calcSharePercentage, decodeText, formatCompactNumber, formatDecimal, formatDuration } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getVisitorOverviewQueryOptions } from '@/services/visitor-insight/get-visitor-overview'

import { OverviewTopVisitors } from './-components/overview/overview-top-visitors'

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
  // Use global filters context for date range and filters (hybrid URL + preferences)
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

  // Get filter fields for 'visitors' group from localized data
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('visitors') as FilterField[]
  }, [wp])

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

  // Handle date range updates from DateRangePicker
  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

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

  // Transform chart format response to data points for LineChart component
  // Chart format: { labels: string[], datasets: [{ key, data, comparison? }] }
  // Previous data comes as separate datasets with key like "visitors_previous" and comparison: true
  // Note: API may return values as strings, so we parse them to numbers
  const chartData = useMemo(() => {
    if (!trafficTrendsResponse?.labels || !trafficTrendsResponse?.datasets) return []

    const labels = trafficTrendsResponse.labels
    const datasets = trafficTrendsResponse.datasets

    // Separate current and previous datasets
    const currentDatasets = datasets.filter((d) => !d.comparison)
    const previousDatasets = datasets.filter((d) => d.comparison)

    return labels.map((label, index) => {
      const point: Record<string, string | number> = { date: label }

      // Add current period data
      currentDatasets.forEach((dataset) => {
        point[dataset.key] = Number(dataset.data[index]) || 0
      })

      // Add previous period data (datasets have keys like "visitors_previous")
      previousDatasets.forEach((dataset) => {
        // Convert "visitors_previous" to "visitorsPrevious"
        const baseKey = dataset.key.replace('_previous', '')
        point[`${baseKey}Previous`] = Number(dataset.data[index]) || 0
      })

      return point
    })
  }, [trafficTrendsResponse])

  // Calculate totals from chart datasets
  // Note: API may return values as strings, so we parse them to numbers
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
      visitors: visitorsDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      visitorsPrevious: visitorsPrevDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      views: viewsDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      viewsPrevious: viewsPrevDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
    }
  }, [trafficTrendsResponse])

  const trafficTrendsMetrics = [
    {
      key: 'visitors',
      label: 'Visitors',
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
      label: 'Views',
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

  // Build metrics from batch response (flat format - totals at top level)
  // Layout: 4 columns, 2 rows
  // Row 1 (with comparison): Visitors, Views, Session Duration, Views Per Session
  // Row 2 (context): Top Country, Top Referrer, Top Search Term, Logged-in Share
  const overviewMetrics = useMemo(() => {
    const totals = metricsResponse?.totals

    if (!totals) return []

    // Extract current and previous values from { current, previous } structure
    const visitors = Number(totals.visitors?.current) || 0
    const views = Number(totals.views?.current) || 0
    const avgSessionDuration = Number(totals.avg_session_duration?.current) || 0
    const pagesPerSession = Number(totals.pages_per_session?.current) || 0

    const prevVisitors = Number(totals.visitors?.previous) || 0
    const prevViews = Number(totals.views?.previous) || 0
    const prevAvgSessionDuration = Number(totals.avg_session_duration?.previous) || 0
    const prevPagesPerSession = Number(totals.pages_per_session?.previous) || 0

    // Context metrics (second row)
    const topCountryName = metricsTopCountry?.items?.[0]?.country_name
    const topReferrerName = metricsTopReferrer?.items?.[0]?.referrer_name
    const topSearchTerm = decodeText(metricsTopSearch?.items?.[0]?.search_term)

    // Calculate logged-in share percentage (capped at 100% to handle data inconsistencies)
    const loggedInVisitors = Number(metricsLoggedIn?.totals?.visitors?.current) || 0
    const prevLoggedInVisitors = Number(metricsLoggedIn?.totals?.visitors?.previous) || 0
    const loggedInShare = calcSharePercentage(loggedInVisitors, visitors)
    const prevLoggedInShare = calcSharePercentage(prevLoggedInVisitors, prevVisitors)

    return [
      // Row 1: Numeric metrics with comparison
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...calcPercentage(visitors, prevVisitors),
      },
      {
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(views),
        ...calcPercentage(views, prevViews),
      },
      {
        label: __('Session Duration', 'wp-statistics'),
        value: formatDuration(avgSessionDuration),
        ...calcPercentage(avgSessionDuration, prevAvgSessionDuration),
      },
      {
        label: __('Views/Session', 'wp-statistics'),
        value: formatDecimal(pagesPerSession),
        ...calcPercentage(pagesPerSession, prevPagesPerSession),
      },
      // Row 2: Context metrics (strings use '-' when empty)
      {
        label: __('Top Country', 'wp-statistics'),
        value: topCountryName || '-',
      },
      {
        label: __('Top Referrer', 'wp-statistics'),
        value: topReferrerName || '-',
      },
      {
        label: __('Top Search Term', 'wp-statistics'),
        value: topSearchTerm || '-',
      },
      {
        label: __('Logged-in Share', 'wp-statistics'),
        value: `${formatDecimal(loggedInShare)}%`,
        ...calcPercentage(loggedInShare, prevLoggedInShare),
      },
    ]
  }, [metricsResponse, metricsTopCountry, metricsTopReferrer, metricsTopSearch, metricsLoggedIn])

  return (
    <div className="min-w-0">
      {/* Header row with title, date picker, and filter button */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Visitor Insights', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && isInitialized && (
            <FilterButton
              fields={filterFields}
              appliedFilters={appliedFilters || []}
              onApplyFilters={handleApplyFilters}
              filterGroup="visitors"
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
            <div className="col-span-12">
              <Panel>
                <Metrics metrics={overviewMetrics} columns={4} />
              </Panel>
            </div>

            <div className="col-span-12">
              <LineChart
                title="Traffic Trends"
                data={chartData}
                metrics={trafficTrendsMetrics}
                showPreviousPeriod={true}
                timeframe={timeframe}
                onTimeframeChange={handleTimeframeChange}
                loading={isChartRefetching}
              />
            </div>

            <div className="col-span-12 lg:col-span-6">
              <HorizontalBarList
                title={__('Top Referrers', 'wp-statistics')}
                items={(() => {
                  const totalVisitors =
                    Number(topReferrersTotals?.visitors?.current ?? topReferrersTotals?.visitors) || 1
                  return topReferrersData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const displayName =
                      item.referrer_name ||
                      item.referrer_domain ||
                      item.referrer_channel ||
                      __('Direct', 'wp-statistics')

                    return {
                      label: displayName,
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: displayName,
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  action: () => console.log('View all referrers'),
                }}
              />
            </div>

            <div className="col-span-12 lg:col-span-6">
              <HorizontalBarList
                title={__('Top Countries', 'wp-statistics')}
                items={(() => {
                  const totalVisitors =
                    Number(topCountriesTotals?.visitors?.current ?? topCountriesTotals?.visitors) || 1
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
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  action: () => console.log('View all countries'),
                }}
              />
            </div>

            <div className="col-span-12 lg:col-span-6">
              <HorizontalBarList
                title={__('Device Type', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(deviceTypeTotals?.visitors?.current ?? deviceTypeTotals?.visitors) || 1
                  return deviceTypeData.map((item) => {
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
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  action: () => console.log('View all device types'),
                }}
              />
            </div>

            <div className="col-span-12 lg:col-span-6">
              <HorizontalBarList
                title={__('Operating Systems', 'wp-statistics')}
                items={(() => {
                  const totalVisitors =
                    Number(operatingSystemsTotals?.visitors?.current ?? operatingSystemsTotals?.visitors) || 1
                  return operatingSystemsData.map((item) => {
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
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  action: () => console.log('View all operating systems'),
                }}
              />
            </div>

            <div className="col-span-12">
              <OverviewTopVisitors data={batchResponse?.data?.items?.top_visitors?.data?.rows} />
            </div>

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
          </div>
        )}
      </div>
    </div>
  )
}
