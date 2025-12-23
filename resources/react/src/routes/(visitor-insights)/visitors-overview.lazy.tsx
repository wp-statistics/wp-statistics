import { useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo, useState } from 'react'

import { type Filter, FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import type { GlobalMapData } from '@/components/custom/global-map'
import { GlobalMap } from '@/components/custom/global-map'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import { Card, CardHeader, CardTitle } from '@/components/ui/card'
import { WordPress } from '@/lib/wordpress'
import { getVisitorInsightGlobalVisitorDistributionQueryOptions } from '@/services/visitor-insight/get-global-visitor-distribution'
import { getVisitorsOverviewBatchQueryOptions } from '@/services/visitor-insight/get-visitors-overview-batch'

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
  const [appliedFilters, setAppliedFilters] = useState<Filter[]>([])
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Get filter fields for 'visitors_overview' group from localized data
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('visitors_overview') as FilterField[]
  }, [])

  const handleRemoveFilter = (filterId: string) => {
    setAppliedFilters((prev) => prev.filter((f) => f.id !== filterId))
  }

  // Calculate date range (last 30 days by default)
  const dateRange = useMemo(() => {
    const now = new Date()
    const thirtyDaysAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
    return {
      dateFrom: thirtyDaysAgo.toISOString().split('T')[0],
      dateTo: now.toISOString().split('T')[0],
    }
  }, [])

  // Batch query for all overview data
  const { data: batchResponse } = useQuery({
    ...getVisitorsOverviewBatchQueryOptions({
      dateFrom: dateRange.dateFrom,
      dateTo: dateRange.dateTo,
      timeframe,
      compare: true,
    }),
    retry: false,
  })

  // Extract data from batch response
  const metricsData = batchResponse?.data?.items?.metrics?.data
  const trafficTrendsData = batchResponse?.data?.items?.traffic_trends?.data?.rows || []
  const topCountriesData = batchResponse?.data?.items?.top_countries?.data?.rows || []
  const deviceTypeData = batchResponse?.data?.items?.device_type?.data?.rows || []
  const operatingSystemsData = batchResponse?.data?.items?.operating_systems?.data?.rows || []

  // Global visitor distribution (still separate query as it needs full country list)
  const { data: globalVisitorDistributionResponse } = useQuery({
    ...getVisitorInsightGlobalVisitorDistributionQueryOptions(),
    retry: false,
  })
  const globalVisitorDistribution = globalVisitorDistributionResponse?.data || { data: { items: [] } }

  // Calculate totals for current and previous period from traffic trends
  const totalVisitors = trafficTrendsData.reduce((sum, item) => sum + (item.visitors || 0), 0)
  const totalVisitorsPrevious = trafficTrendsData.reduce((sum, item) => sum + (item.previous?.visitors || 0), 0)
  const totalViews = trafficTrendsData.reduce((sum, item) => sum + (item.views || 0), 0)
  const totalViewsPrevious = trafficTrendsData.reduce((sum, item) => sum + (item.previous?.views || 0), 0)

  const trafficTrendsMetrics = [
    {
      key: 'visitors',
      label: 'Visitors',
      color: 'var(--chart-1)',
      enabled: true,
      value: totalVisitors >= 1000 ? `${(totalVisitors / 1000).toFixed(1)}k` : totalVisitors.toFixed(1),
      previousValue:
        totalVisitorsPrevious >= 1000
          ? `${(totalVisitorsPrevious / 1000).toFixed(1)}k`
          : totalVisitorsPrevious.toFixed(1),
    },
    {
      key: 'views',
      label: 'Views',
      color: 'var(--chart-4)',
      enabled: true,
      value: totalViews >= 1000 ? `${(totalViews / 1000).toFixed(1)}k` : totalViews.toFixed(1),
      previousValue:
        totalViewsPrevious >= 1000 ? `${(totalViewsPrevious / 1000).toFixed(1)}k` : totalViewsPrevious.toFixed(1),
    },
  ]

  // Transform traffic trends data for the chart component
  const chartData = trafficTrendsData.map((item) => ({
    date: item.date,
    visitors: item.visitors,
    views: item.views,
    visitorsPrevious: item.previous?.visitors || 0,
    viewsPrevious: item.previous?.views || 0,
  }))

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
    if (previous === 0) return { value: '0', isNegative: false }
    const change = ((current - previous) / previous) * 100
    return {
      value: Math.abs(change).toFixed(1),
      isNegative: change < 0,
    }
  }

  // Build metrics from batch response
  const overviewMetrics = useMemo(() => {
    const totals = metricsData?.totals
    const previousTotals = totals?.previous

    if (!totals) return []

    const visitors = totals.visitors || 0
    const views = totals.views || 0
    const sessions = totals.sessions || 0
    const avgSessionDuration = totals.avg_session_duration || 0
    const pagesPerSession = totals.pages_per_session || 0
    const bounceRate = totals.bounce_rate || 0

    const prevVisitors = previousTotals?.visitors || 0
    const prevViews = previousTotals?.views || 0
    const prevSessions = previousTotals?.sessions || 0
    const prevAvgSessionDuration = previousTotals?.avg_session_duration || 0
    const prevPagesPerSession = previousTotals?.pages_per_session || 0
    const prevBounceRate = previousTotals?.bounce_rate || 0

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
  }, [metricsData])

  return (
    <div className="min-w-0">
      {/* Header row with title and filter button */}
      <div className="flex items-center justify-between p-4 bg-white border-b border-input">
        <h1 className="text-2xl font-medium text-neutral-700">{__('Visitor Insights', 'wp-statistics')}</h1>
        {filterFields.length > 0 && (
          <FilterButton fields={filterFields} appliedFilters={appliedFilters} onApplyFilters={setAppliedFilters} />
        )}
      </div>

      <div className="p-4">
        {/* Applied filters row (separate from button) */}
        {appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} className="mb-4" />
        )}

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
            />
          </div>

          <div className="col-span-6">
            <Card>
              <CardHeader>
                <CardTitle>Top Entry Pages</CardTitle>
              </CardHeader>
            </Card>
          </div>

          <div className="col-span-6">
            <Card>
              <CardHeader>
                <CardTitle>Top Referrers</CardTitle>
              </CardHeader>
            </Card>
          </div>

          <div className="col-span-4">
            <HorizontalBarList
              title={__('Top Countries', 'wp-statistics')}
              items={topCountriesData.map((item) => {
                const currentValue = item.visitors || 0
                const previousValue = item.previous?.visitors || 0

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
                const currentValue = item.visitors || 0
                const previousValue = item.previous?.visitors || 0

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
                const currentValue = item.visitors || 0
                const previousValue = item.previous?.visitors || 0

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
      </div>
    </div>
  )
}
