import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useState } from 'react'

import { DateRangePicker, type DateRange } from '@/components/custom/date-range-picker'
import { type Filter, FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import type { GlobalMapData } from '@/components/custom/global-map'
import { GlobalMap } from '@/components/custom/global-map'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { formatDateForAPI } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getVisitorInsightGlobalVisitorDistributionQueryOptions } from '@/services/visitor-insight/get-global-visitor-distribution'
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

// Convert URL filter format to Filter type
const urlFiltersToFilters = (
  urlFilters: Array<{ field: string; operator: string; value: string | string[] }> | undefined,
  filterFields: FilterField[]
): Filter[] => {
  if (!urlFilters || !Array.isArray(urlFilters) || urlFilters.length === 0) return []

  return urlFilters.map((urlFilter, index) => {
    const field = filterFields.find((f) => f.name === urlFilter.field)
    const label = field?.label || urlFilter.field

    // Get display value from field options if available
    let displayValue = Array.isArray(urlFilter.value) ? urlFilter.value.join(', ') : urlFilter.value
    if (field?.options) {
      const values = Array.isArray(urlFilter.value) ? urlFilter.value : [urlFilter.value]
      const labels = values.map((v) => field.options?.find((o) => String(o.value) === v)?.label || v).join(', ')
      displayValue = labels
    }

    // Create filter ID in the expected format: field-field-filter-restored-index
    const filterId = `${urlFilter.field}-${urlFilter.field}-filter-restored-${index}`

    return {
      id: filterId,
      label,
      operator: urlFilter.operator,
      rawOperator: urlFilter.operator,
      value: displayValue,
      rawValue: urlFilter.value,
    }
  })
}

// Extract the field name from filter ID
// Filter IDs are in format: "field_name-field_name-filter-..." or "field_name-index"
const extractFilterField = (filterId: string): string => {
  return filterId.split('-')[0]
}

// Convert Filter type to URL filter format
const filtersToUrlFilters = (
  filters: Filter[]
): Array<{ field: string; operator: string; value: string | string[] }> => {
  return filters.map((filter) => ({
    field: extractFilterField(filter.id),
    operator: filter.rawOperator || filter.operator,
    value: filter.rawValue || filter.value,
  }))
}

function RouteComponent() {
  const navigate = useNavigate()
  const { filters: urlFilters } = Route.useSearch()

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  // Get filter fields for 'visitors' group from localized data
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('visitors') as FilterField[]
  }, [])

  // Initialize filters from URL or use empty array
  const [appliedFilters, setAppliedFilters] = useState<Filter[]>(() => {
    return urlFiltersToFilters(urlFilters, filterFields)
  })

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Sync filters to URL when they change
  useEffect(() => {
    const urlFilterData = filtersToUrlFilters(appliedFilters)
    navigate({
      search: (prev) => ({
        ...prev,
        filters: urlFilterData.length > 0 ? urlFilterData : undefined,
      }),
      replace: true,
    })
  }, [appliedFilters, navigate])

  const handleRemoveFilter = (filterId: string) => {
    setAppliedFilters((prev) => prev.filter((f) => f.id !== filterId))
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

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange }) => {
      setDateRange(values.range)
      setCompareDateRange(values.rangeCompare)
    },
    []
  )

  // Batch query for all overview data
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
      filters: appliedFilters,
    }),
    retry: false,
    placeholderData: keepPreviousData, // Keep showing old data while fetching new data
  })

  // Only show skeleton on initial load (no data yet), not on refetches
  const showSkeleton = isLoading && !batchResponse
  // Show loading indicator on chart when refetching (e.g., timeframe change)
  const isChartRefetching = isFetching && !isLoading

  // Extract data from batch response
  // Batch API returns: { success, items: { metrics, traffic_trends, ... } }
  // axios wraps in { data: responseBody }, so batchResponse.data = API response
  // Metrics uses flat format - totals at top level
  const metricsResponse = batchResponse?.data?.items?.metrics
  // Traffic trends uses chart format - labels and datasets at top level
  const trafficTrendsResponse = batchResponse?.data?.items?.traffic_trends
  // Table format queries - each has data.rows structure inside
  const topCountriesData = batchResponse?.data?.items?.top_countries?.data?.rows || []
  const deviceTypeData = batchResponse?.data?.items?.device_type?.data?.rows || []
  const operatingSystemsData = batchResponse?.data?.items?.operating_systems?.data?.rows || []
  const topEntryPagesData = batchResponse?.data?.items?.top_entry_pages?.data?.rows || []
  const topReferrersData = batchResponse?.data?.items?.top_referrers?.data?.rows || []

  // Global visitor distribution (still separate query as it needs full country list)
  const { data: globalVisitorDistributionResponse } = useQuery({
    ...getVisitorInsightGlobalVisitorDistributionQueryOptions(),
    retry: false,
  })
  const globalVisitorDistribution = globalVisitorDistributionResponse?.data || { data: { items: [] } }

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
        chartTotals.visitors >= 1000 ? `${(chartTotals.visitors / 1000).toFixed(1)}k` : chartTotals.visitors.toFixed(1),
      previousValue:
        chartTotals.visitorsPrevious >= 1000
          ? `${(chartTotals.visitorsPrevious / 1000).toFixed(1)}k`
          : chartTotals.visitorsPrevious.toFixed(1),
    },
    {
      key: 'views',
      label: 'Views',
      color: 'var(--chart-4)',
      enabled: true,
      value: chartTotals.views >= 1000 ? `${(chartTotals.views / 1000).toFixed(1)}k` : chartTotals.views.toFixed(1),
      previousValue:
        chartTotals.viewsPrevious >= 1000
          ? `${(chartTotals.viewsPrevious / 1000).toFixed(1)}k`
          : chartTotals.viewsPrevious.toFixed(1),
    },
  ]

  // Transform global visitor distribution data for the map
  const globalMapData: GlobalMapData = {
    countries: (globalVisitorDistribution.data.items || [])
      .filter((item) => item.code && item.name && item.visitors)
      .map((item) => ({
        code: item.code.toLowerCase(),
        name: item.name,
        visitors: Number(item.visitors),
      })),
  }

  // Helper function to format numbers
  const formatNumber = (num: number) => {
    if (num >= 1000000) return `${(num / 1000000).toFixed(1)}M`
    if (num >= 1000) return `${(num / 1000).toFixed(1)}k`
    return num.toLocaleString()
  }

  // Helper function to format duration (seconds to mm:ss)
  const formatDuration = (seconds: number) => {
    const mins = Math.floor(seconds / 60)
    const secs = Math.floor(seconds % 60)
    return `${mins}:${secs.toString().padStart(2, '0')}`
  }

  // Calculate percentage change
  const calcPercentage = (current: number, previous: number) => {
    if (previous === 0) return { percentage: '0', isNegative: false }
    const change = ((current - previous) / previous) * 100
    return {
      percentage: Math.abs(change).toFixed(1),
      isNegative: change < 0,
    }
  }

  // Build metrics from batch response (flat format - totals at top level)
  // Totals structure: { metric: { current, previous } }
  // Note: API may return values as strings, so we parse them to numbers
  const overviewMetrics = useMemo(() => {
    const totals = metricsResponse?.totals

    if (!totals) return []

    // Extract current and previous values from { current, previous } structure
    const visitors = Number(totals.visitors?.current) || 0
    const views = Number(totals.views?.current) || 0
    const sessions = Number(totals.sessions?.current) || 0
    const avgSessionDuration = Number(totals.avg_session_duration?.current) || 0
    const pagesPerSession = Number(totals.pages_per_session?.current) || 0
    const bounceRate = Number(totals.bounce_rate?.current) || 0

    const prevVisitors = Number(totals.visitors?.previous) || 0
    const prevViews = Number(totals.views?.previous) || 0
    const prevSessions = Number(totals.sessions?.previous) || 0
    const prevAvgSessionDuration = Number(totals.avg_session_duration?.previous) || 0
    const prevPagesPerSession = Number(totals.pages_per_session?.previous) || 0
    const prevBounceRate = Number(totals.bounce_rate?.previous) || 0

    return [
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
        label: __('Sessions', 'wp-statistics'),
        value: formatNumber(sessions),
        ...calcPercentage(sessions, prevSessions),
        tooltipContent: __('Total number of sessions', 'wp-statistics'),
      },
      {
        label: __('Avg. Session Duration', 'wp-statistics'),
        value: formatDuration(avgSessionDuration),
        ...calcPercentage(avgSessionDuration, prevAvgSessionDuration),
        tooltipContent: __('Average duration of a user session', 'wp-statistics'),
      },
      {
        label: __('Pages/Session', 'wp-statistics'),
        value: pagesPerSession.toFixed(2),
        ...calcPercentage(pagesPerSession, prevPagesPerSession),
        tooltipContent: __('Average pages viewed per session', 'wp-statistics'),
      },
      {
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${bounceRate.toFixed(1)}%`,
        ...calcPercentage(bounceRate, prevBounceRate),
        tooltipContent: __('Percentage of single-page sessions', 'wp-statistics'),
      },
    ]
  }, [metricsResponse])

  return (
    <div className="min-w-0">
      {/* Header row with title, date picker, and filter button */}
      <div className="flex items-center justify-between p-4 bg-white border-b border-input">
        <h1 className="text-2xl font-medium text-neutral-700">{__('Visitor Insights', 'wp-statistics')}</h1>
        <div className="flex items-center gap-2">
          {filterFields.length > 0 && (
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

      <div className="p-4">
        {/* Applied filters row (separate from button) */}
        {appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} className="mb-4" />
        )}

        {showSkeleton ? (
          <div className="grid gap-3 grid-cols-12">
            {/* Metrics skeleton */}
            <div className="col-span-12">
              <Card>
                <CardContent className="p-4">
                  <div className="grid grid-cols-3 gap-4">
                    {[...Array(6)].map((_, i) => (
                      <div key={i} className="space-y-2">
                        <Skeleton className="h-4 w-24" />
                        <Skeleton className="h-8 w-32" />
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </div>
            {/* Chart skeleton */}
            <div className="col-span-12">
              <Card>
                <CardHeader>
                  <Skeleton className="h-6 w-32" />
                </CardHeader>
                <CardContent>
                  <Skeleton className="h-64 w-full" />
                </CardContent>
              </Card>
            </div>
            {/* Lists skeleton */}
            {[...Array(6)].map((_, i) => (
              <div key={i} className={i < 2 ? 'col-span-6' : 'col-span-4'}>
                <Card>
                  <CardHeader>
                    <Skeleton className="h-5 w-28" />
                  </CardHeader>
                  <CardContent className="space-y-3">
                    {[...Array(5)].map((_, j) => (
                      <div key={j} className="flex justify-between items-center">
                        <Skeleton className="h-4 w-32" />
                        <Skeleton className="h-4 w-16" />
                      </div>
                    ))}
                  </CardContent>
                </Card>
              </div>
            ))}
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <Metrics metrics={overviewMetrics} columns={3} />
            </div>

            <div className="col-span-12">
              <LineChart
                title="Traffic Trends"
                data={chartData}
                metrics={trafficTrendsMetrics}
                showPreviousPeriod={true}
                timeframe={timeframe}
                onTimeframeChange={setTimeframe}
                loading={isChartRefetching}
              />
            </div>

            <div className="col-span-6">
              <HorizontalBarList
                title={__('Top Pages', 'wp-statistics')}
                items={topEntryPagesData.map((item) => {
                  // API returns values as strings, convert to numbers
                  const currentValue = Number(item.visitors) || 0
                  const previousValue = Number(item.previous?.visitors) || 0

                  const percentageChange =
                    previousValue > 0 ? (((currentValue - previousValue) / previousValue) * 100).toFixed(1) : '0'
                  const isNegative = currentValue < previousValue

                  return {
                    label: item.page_title || item.page_uri || __('Unknown', 'wp-statistics'),
                    value: currentValue.toLocaleString(),
                    percentage: Math.abs(parseFloat(percentageChange)).toString(),
                    isNegative,
                    tooltipTitle: item.page_title || item.page_uri || '',
                    tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                  }
                })}
                link={{
                  title: __('View Pages', 'wp-statistics'),
                  action: () => console.log('View all pages'),
                }}
              />
            </div>

            <div className="col-span-6">
              <HorizontalBarList
                title={__('Top Referrers', 'wp-statistics')}
                items={topReferrersData.map((item) => {
                  // API returns values as strings, convert to numbers
                  const currentValue = Number(item.visitors) || 0
                  const previousValue = Number(item.previous?.visitors) || 0

                  const percentageChange =
                    previousValue > 0 ? (((currentValue - previousValue) / previousValue) * 100).toFixed(1) : '0'
                  const isNegative = currentValue < previousValue

                  // Display referrer name or domain, fallback to channel
                  const displayName =
                    item.referrer_name || item.referrer_domain || item.referrer_channel || __('Direct', 'wp-statistics')

                  return {
                    label: displayName,
                    value: currentValue.toLocaleString(),
                    percentage: Math.abs(parseFloat(percentageChange)).toString(),
                    isNegative,
                    tooltipTitle: displayName,
                    tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                  }
                })}
                link={{
                  title: __('View Referrers', 'wp-statistics'),
                  action: () => console.log('View all referrers'),
                }}
              />
            </div>

            <div className="col-span-4">
              <HorizontalBarList
                title={__('Top Countries', 'wp-statistics')}
                items={topCountriesData.map((item) => {
                  // API returns values as strings, convert to numbers
                  const currentValue = Number(item.visitors) || 0
                  const previousValue = Number(item.previous?.visitors) || 0

                  const percentageChange =
                    previousValue > 0 ? (((currentValue - previousValue) / previousValue) * 100).toFixed(1) : '0'
                  const isNegative = currentValue < previousValue

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
                    percentage: Math.abs(parseFloat(percentageChange)).toString(),
                    isNegative,
                    tooltipTitle: item.country_name || '',
                    tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                  }
                })}
                link={{
                  title: __('View Countries', 'wp-statistics'),
                  action: () => console.log('View all countries'),
                }}
              />
            </div>

            <div className="col-span-4">
              <HorizontalBarList
                title={__('Device Type', 'wp-statistics')}
                items={deviceTypeData.map((item) => {
                  // API returns values as strings, convert to numbers
                  const currentValue = Number(item.visitors) || 0
                  const previousValue = Number(item.previous?.visitors) || 0

                  const percentageChange =
                    previousValue > 0 ? (((currentValue - previousValue) / previousValue) * 100).toFixed(1) : '0'
                  const isNegative = currentValue < previousValue

                  // Map device type to icon name
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
                    percentage: Math.abs(parseFloat(percentageChange)).toString(),
                    isNegative,
                    tooltipTitle: item.device_type_name || '',
                    tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                  }
                })}
                link={{
                  title: __('View Device Types', 'wp-statistics'),
                  action: () => console.log('View all device types'),
                }}
              />
            </div>

            <div className="col-span-4">
              <HorizontalBarList
                title={__('Operating Systems', 'wp-statistics')}
                items={operatingSystemsData.map((item) => {
                  // API returns values as strings, convert to numbers
                  const currentValue = Number(item.visitors) || 0
                  const previousValue = Number(item.previous?.visitors) || 0

                  const percentageChange =
                    previousValue > 0 ? (((currentValue - previousValue) / previousValue) * 100).toFixed(1) : '0'
                  const isNegative = currentValue < previousValue

                  // Map OS name to icon name (lowercase, replace spaces with underscores)
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
                    percentage: Math.abs(parseFloat(percentageChange)).toString(),
                    isNegative,
                    tooltipTitle: item.os_name || '',
                    tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                  }
                })}
                link={{
                  title: __('View Operating Systems', 'wp-statistics'),
                  action: () => console.log('View all operating systems'),
                }}
              />
            </div>

            <div className="col-span-12">
              <OverviewTopVisitors data={batchResponse?.data?.items?.top_visitors?.data?.rows} />
            </div>

            <Card className="col-span-6">
              <CardHeader>
                <CardTitle>Traffic by Hour</CardTitle>
              </CardHeader>
            </Card>

            <div className="col-span-6">
              <GlobalMap
                data={globalMapData}
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
