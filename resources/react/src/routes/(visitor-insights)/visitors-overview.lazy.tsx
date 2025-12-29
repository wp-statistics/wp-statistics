import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { DateRangePicker, type DateRange } from '@/components/custom/date-range-picker'
import { type Filter, FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField, getOperatorDisplay } from '@/components/custom/filter-button'
import { GlobalMap } from '@/components/custom/global-map'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import { Panel } from '@/components/ui/panel'
import { Skeleton } from '@/components/ui/skeleton'
import { filtersToUrlFilters, urlFiltersToFilters } from '@/lib/filter-utils'
import { formatDateForAPI, formatDuration, formatDecimal } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getVisitorOverviewQueryOptions } from '@/services/visitor-insight/get-visitor-overview'

import { OverviewTopVisitors } from './-components/overview/overview-top-visitors'

export const Route = createLazyFileRoute('/(visitor-insights)/visitors-overview')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-red-600 mb-2">Error Loading Page</h2>
      <p className="text-gray-600">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  const navigate = useNavigate()
  const { filters: urlFilters } = Route.useSearch()

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  // Get filter fields for 'visitors' group from localized data
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('visitors') as FilterField[]
  }, [])

  // Initialize filters state - null until URL sync is complete
  const [appliedFilters, setAppliedFilters] = useState<Filter[] | null>(null)
  const lastSyncedFiltersRef = useRef<string | null>(null)

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Sync filters FROM URL on mount (only once)
  useEffect(() => {
    if (lastSyncedFiltersRef.current !== null) return // Already initialized

    const filtersFromUrl = urlFiltersToFilters(urlFilters, filterFields)
    setAppliedFilters(filtersFromUrl)
    lastSyncedFiltersRef.current = JSON.stringify(urlFilters || [])
  }, [urlFilters, filterFields])

  // Sync filters TO URL when they change (only after initialization)
  useEffect(() => {
    if (lastSyncedFiltersRef.current === null || appliedFilters === null) return

    const urlFilterData = filtersToUrlFilters(appliedFilters)
    const serialized = JSON.stringify(urlFilterData)

    if (serialized === lastSyncedFiltersRef.current) return

    lastSyncedFiltersRef.current = serialized
    navigate({
      search: (prev) => ({
        ...prev,
        filters: urlFilterData.length > 0 ? urlFilterData : undefined,
      }),
      replace: true,
    })
  }, [appliedFilters, navigate])

  const handleRemoveFilter = (filterId: string) => {
    setAppliedFilters((prev) => (prev ? prev.filter((f) => f.id !== filterId) : []))
  }

  // Date range state (default to today)
  const [dateRange, setDateRange] = useState<DateRange>(() => {
    const today = new Date()
    return {
      from: today,
      to: today,
    }
  })

  // Compare date range state (off by default)
  const [compareDateRange, setCompareDateRange] = useState<DateRange | undefined>(undefined)

  // Track if only timeframe changed (for loading behavior)
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevFiltersRef = useRef<string>(JSON.stringify(appliedFilters))
  const prevDateRangeRef = useRef<string>(JSON.stringify(dateRange))
  const prevCompareDateRangeRef = useRef<string>(JSON.stringify(compareDateRange))

  // Detect what changed when data is being fetched
  useEffect(() => {
    const currentFilters = JSON.stringify(appliedFilters)
    const currentDateRange = JSON.stringify(dateRange)
    const currentCompareDateRange = JSON.stringify(compareDateRange)

    const filtersChanged = currentFilters !== prevFiltersRef.current
    const dateRangeChanged = currentDateRange !== prevDateRangeRef.current
    const compareDateRangeChanged = currentCompareDateRange !== prevCompareDateRangeRef.current

    // If filters or dates changed, it's NOT a timeframe-only change
    if (filtersChanged || dateRangeChanged || compareDateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    // Update refs
    prevFiltersRef.current = currentFilters
    prevDateRangeRef.current = currentDateRange
    prevCompareDateRangeRef.current = currentCompareDateRange
  }, [appliedFilters, dateRange, compareDateRange])

  // Custom timeframe setter that tracks the change type
  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange }) => {
      setDateRange(values.range)
      setCompareDateRange(values.rangeCompare)
    },
    []
  )

  // Batch query for all overview data (only when filters are initialized)
  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getVisitorOverviewQueryOptions({
      dateFrom: formatDateForAPI(dateRange.from),
      dateTo: formatDateForAPI(dateRange.to || dateRange.from),
      compareDateFrom: compareDateRange ? formatDateForAPI(compareDateRange.from) : undefined,
      compareDateTo: compareDateRange ? formatDateForAPI(compareDateRange.to || compareDateRange.from) : undefined,
      timeframe,
      filters: appliedFilters || [],
    }),
    retry: false,
    placeholderData: keepPreviousData, // Keep showing old data while fetching new data
    enabled: appliedFilters !== null,
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
      color: 'var(--chart-4)',
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

  // Helper function to format numbers
  const formatNumber = (num: number) => {
    if (num >= 1000000) return `${formatDecimal(num / 1000000)}M`
    if (num >= 1000) return `${formatDecimal(num / 1000)}k`
    return num.toLocaleString()
  }

  // Calculate percentage change: ((Current - Previous) / Previous) × 100
  const calcPercentage = (current: number, previous: number) => {
    // If both are 0, no change
    if (previous === 0 && current === 0) {
      return { percentage: '0', isNegative: false }
    }
    // If previous is 0 but current > 0, show 100% increase
    if (previous === 0) {
      return { percentage: '100', isNegative: false }
    }
    const change = ((current - previous) / previous) * 100
    return {
      percentage: formatDecimal(Math.abs(change)),
      isNegative: change < 0,
    }
  }

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
    const topSearchTerm = metricsTopSearch?.items?.[0]?.search_term

    // Calculate logged-in share percentage
    const loggedInVisitors = Number(metricsLoggedIn?.totals?.visitors?.current) || 0
    const prevLoggedInVisitors = Number(metricsLoggedIn?.totals?.visitors?.previous) || 0
    const loggedInShare = visitors > 0 ? (loggedInVisitors / visitors) * 100 : 0
    const prevLoggedInShare = prevVisitors > 0 ? (prevLoggedInVisitors / prevVisitors) * 100 : 0

    return [
      // Row 1: Numeric metrics with comparison
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatNumber(visitors),
        ...calcPercentage(visitors, prevVisitors),
        tooltipContent: __('Total number of unique visitors', 'wp-statistics'),
      },
      {
        label: __('Views', 'wp-statistics'),
        value: formatNumber(views),
        ...calcPercentage(views, prevViews),
        tooltipContent: __('Total page views', 'wp-statistics'),
      },
      {
        label: __('Session Duration', 'wp-statistics'),
        value: formatDuration(avgSessionDuration),
        ...calcPercentage(avgSessionDuration, prevAvgSessionDuration),
        tooltipContent: __('Average duration of a user session', 'wp-statistics'),
      },
      {
        label: __('Views/Session', 'wp-statistics'),
        value: formatDecimal(pagesPerSession),
        ...calcPercentage(pagesPerSession, prevPagesPerSession),
        tooltipContent: __('Average pages viewed per session', 'wp-statistics'),
      },
      // Row 2: Context metrics (strings use '-' when empty)
      {
        label: __('Top Country', 'wp-statistics'),
        value: topCountryName || '-',
        tooltipContent: __('Country with the most visitors', 'wp-statistics'),
      },
      {
        label: __('Top Referrer', 'wp-statistics'),
        value: topReferrerName || '-',
        tooltipContent: __('Top traffic source', 'wp-statistics'),
      },
      {
        label: __('Top Search Term', 'wp-statistics'),
        value: topSearchTerm || '-',
        tooltipContent: __('Most popular search term', 'wp-statistics'),
      },
      {
        label: __('Logged-in Share', 'wp-statistics'),
        value: `${formatDecimal(loggedInShare)}%`,
        ...calcPercentage(loggedInShare, prevLoggedInShare),
        tooltipContent: __('Percentage of logged-in visitors', 'wp-statistics'),
      },
    ]
  }, [metricsResponse, metricsTopCountry, metricsTopReferrer, metricsTopSearch, metricsLoggedIn])

  return (
    <div className="min-w-0">
      {/* Header row with title, date picker, and filter button */}
      <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
        <h1 className="text-xl font-semibold text-neutral-800">{__('Visitor Insights', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && appliedFilters !== null && (
            <FilterButton fields={filterFields} appliedFilters={appliedFilters} onApplyFilters={setAppliedFilters} />
          )}
          <DateRangePicker
            initialDateFrom={dateRange.from}
            initialDateTo={dateRange.to}
            showCompare={true}
            onUpdate={handleDateRangeUpdate}
            align="end"
          />
        </div>
      </div>

      <div className="p-2">
        {/* Applied filters row (separate from button) */}
        {appliedFilters && appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} className="mb-2" />
        )}

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-2 grid-cols-12">
            {/* Metrics skeleton - 4 columns, 2 rows */}
            <div className="col-span-12">
              <Panel className="p-4">
                <div className="grid grid-cols-4 gap-4">
                  {[...Array(8)].map((_, i) => (
                    <div key={i} className="space-y-2">
                      <Skeleton className="h-4 w-24" />
                      <Skeleton className="h-8 w-32" />
                    </div>
                  ))}
                </div>
              </Panel>
            </div>
            {/* Chart skeleton */}
            <div className="col-span-12">
              <Panel>
                <div className="px-4 pt-4 pb-3">
                  <Skeleton className="h-5 w-32" />
                </div>
                <div className="px-4 pb-4">
                  <Skeleton className="h-64 w-full" />
                </div>
              </Panel>
            </div>
            {/* Top Referrers skeleton - full width */}
            <div className="col-span-12">
              <Panel>
                <div className="px-4 pt-4 pb-3">
                  <Skeleton className="h-5 w-28" />
                </div>
                <div className="px-4 pb-4 space-y-3">
                  {[...Array(5)].map((_, j) => (
                    <div key={j} className="flex justify-between items-center">
                      <Skeleton className="h-4 w-32" />
                      <Skeleton className="h-4 w-16" />
                    </div>
                  ))}
                </div>
              </Panel>
            </div>
            {/* Three column lists skeleton */}
            {[...Array(3)].map((_, i) => (
              <div key={i} className="col-span-4">
                <Panel>
                  <div className="px-4 pt-4 pb-3">
                    <Skeleton className="h-5 w-28" />
                  </div>
                  <div className="px-4 pb-4 space-y-3">
                    {[...Array(5)].map((_, j) => (
                      <div key={j} className="flex justify-between items-center">
                        <Skeleton className="h-4 w-32" />
                        <Skeleton className="h-4 w-16" />
                      </div>
                    ))}
                  </div>
                </Panel>
              </div>
            ))}
            {/* Top Visitors skeleton - full width */}
            <div className="col-span-12">
              <Panel>
                <div className="px-4 pt-4 pb-3">
                  <Skeleton className="h-5 w-28" />
                </div>
                <div className="px-4 pb-4">
                  <Skeleton className="h-48 w-full" />
                </div>
              </Panel>
            </div>
            {/* Global Map skeleton - full width */}
            <div className="col-span-12">
              <Panel>
                <div className="px-4 pt-4 pb-3">
                  <Skeleton className="h-5 w-40" />
                </div>
                <div className="px-4 pb-4">
                  <Skeleton className="h-64 w-full" />
                </div>
              </Panel>
            </div>
          </div>
        ) : (
          <div className="grid gap-2 grid-cols-12">
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

            <div className="col-span-12">
              <HorizontalBarList
                title={__('Top Referrers', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topReferrersTotals?.visitors?.current ?? topReferrersTotals?.visitors) || 1
                  return topReferrersData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const displayName =
                      item.referrer_name || item.referrer_domain || item.referrer_channel || __('Direct', 'wp-statistics')

                    return {
                      label: displayName,
                      value: currentValue.toLocaleString(),
                      percentage,
                      fillPercentage: (currentValue / totalVisitors) * 100,
                      isNegative,
                      tooltipTitle: displayName,
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Referrers', 'wp-statistics'),
                  action: () => console.log('View all referrers'),
                }}
              />
            </div>

            <div className="col-span-4">
              <HorizontalBarList
                title={__('Top Countries', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topCountriesTotals?.visitors?.current ?? topCountriesTotals?.visitors) || 1
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
                      value: currentValue.toLocaleString(),
                      percentage,
                      fillPercentage: (currentValue / totalVisitors) * 100,
                      isNegative,
                      tooltipTitle: item.country_name || '',
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Countries', 'wp-statistics'),
                  action: () => console.log('View all countries'),
                }}
              />
            </div>

            <div className="col-span-4">
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
                      value: currentValue.toLocaleString(),
                      percentage,
                      fillPercentage: (currentValue / totalVisitors) * 100,
                      isNegative,
                      tooltipTitle: item.device_type_name || '',
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Device Types', 'wp-statistics'),
                  action: () => console.log('View all device types'),
                }}
              />
            </div>

            <div className="col-span-4">
              <HorizontalBarList
                title={__('Operating Systems', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(operatingSystemsTotals?.visitors?.current ?? operatingSystemsTotals?.visitors) || 1
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
                      value: currentValue.toLocaleString(),
                      percentage,
                      fillPercentage: (currentValue / totalVisitors) * 100,
                      isNegative,
                      tooltipTitle: item.os_name || '',
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Operating Systems', 'wp-statistics'),
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
                dateFrom={formatDateForAPI(dateRange.from)}
                dateTo={formatDateForAPI(dateRange.to || dateRange.from)}
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
