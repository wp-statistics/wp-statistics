import { useQuery,useSuspenseQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo,useState } from 'react'

import { type Filter,FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import type { GlobalMapData } from '@/components/custom/global-map'
import { GlobalMap } from '@/components/custom/global-map'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import { Card, CardHeader, CardTitle } from '@/components/ui/card'
import { WordPress } from '@/lib/wordpress'
import { getVisitorInsightDevicesTypeQueryOptions } from '@/services/visitor-insight/get-devices-type'
import { getVisitorInsightGlobalVisitorDistributionQueryOptions } from '@/services/visitor-insight/get-global-visitor-distribution'
import { getVisitorInsightOSSQueryOptions } from '@/services/visitor-insight/get-oss'
import { getVisitorInsightTopCountriesQueryOptions } from '@/services/visitor-insight/get-top-countries'
import { getVisitorInsightTrafficTrendsQueryOptions } from '@/services/visitor-insight/get-traffic-trends'

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

  // Use useQuery with fallbacks for potentially failing endpoints
  const { data: topCountriesResponse } = useQuery({
    ...getVisitorInsightTopCountriesQueryOptions(),
    retry: false,
  })
  const topCountries = topCountriesResponse?.data || { data: { items: [] } }

  const { data: devicesTypeResponse } = useQuery({
    ...getVisitorInsightDevicesTypeQueryOptions(),
    retry: false,
  })
  const devicesType = devicesTypeResponse?.data || { data: { items: [] } }

  const { data: ossResponse } = useQuery({
    ...getVisitorInsightOSSQueryOptions(),
    retry: false,
  })
  const oss = ossResponse?.data || { data: { items: [] } }

  const { data: globalVisitorDistributionResponse } = useQuery({
    ...getVisitorInsightGlobalVisitorDistributionQueryOptions(),
    retry: false,
  })
  const globalVisitorDistribution = globalVisitorDistributionResponse?.data || { data: { items: [] } }

  const { data: trafficTrendsResponse } = useQuery({
    ...getVisitorInsightTrafficTrendsQueryOptions({ range: timeframe }),
    retry: false,
  })
  const trafficTrends = trafficTrendsResponse?.data || { data: { items: [] } }

  const trafficTrendsData = trafficTrends.data.items || []

  // Calculate totals for current and previous period
  const totalVisitors = trafficTrendsData.reduce((sum, item) => sum + (item.visitors || 0), 0)
  const totalVisitorsPrevious = trafficTrendsData.reduce((sum, item) => sum + (item.visitorsPrevious || 0), 0)
  const totalViews = trafficTrendsData.reduce((sum, item) => sum + (item.views || 0), 0)
  const totalViewsPrevious = trafficTrendsData.reduce((sum, item) => sum + (item.viewsPrevious || 0), 0)

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

  // Use real data if available, otherwise use fake data for demonstration
  const hasRealData = globalVisitorDistribution.data.items && globalVisitorDistribution.data.items.length > 0

  const fakeCountryData = [
    { code: 'us', name: 'United States', visitors: 25000 },
    { code: 'gb', name: 'United Kingdom', visitors: 18000 },
    { code: 'de', name: 'Germany', visitors: 15000 },
    { code: 'fr', name: 'France', visitors: 14000 },
    { code: 'ca', name: 'Canada', visitors: 12000 },
    { code: 'au', name: 'Australia', visitors: 10000 },
    { code: 'jp', name: 'Japan', visitors: 9000 },
    { code: 'in', name: 'India', visitors: 8000 },
    { code: 'br', name: 'Brazil', visitors: 7500 },
    { code: 'it', name: 'Italy', visitors: 7000 },
    { code: 'es', name: 'Spain', visitors: 6500 },
    { code: 'mx', name: 'Mexico', visitors: 6000 },
    { code: 'nl', name: 'Netherlands', visitors: 5500 },
    { code: 'se', name: 'Sweden', visitors: 5000 },
    { code: 'ch', name: 'Switzerland', visitors: 4500 },
    { code: 'be', name: 'Belgium', visitors: 4000 },
    { code: 'pl', name: 'Poland', visitors: 3500 },
    { code: 'at', name: 'Austria', visitors: 3000 },
    { code: 'no', name: 'Norway', visitors: 2800 },
    { code: 'dk', name: 'Denmark', visitors: 2500 },
    { code: 'fi', name: 'Finland', visitors: 2200 },
    { code: 'ie', name: 'Ireland', visitors: 2000 },
    { code: 'pt', name: 'Portugal', visitors: 1800 },
    { code: 'gr', name: 'Greece', visitors: 1500 },
    { code: 'cz', name: 'Czech Republic', visitors: 1400 },
    { code: 'ro', name: 'Romania', visitors: 1200 },
    { code: 'hu', name: 'Hungary', visitors: 1100 },
    { code: 'nz', name: 'New Zealand', visitors: 1000 },
    { code: 'sg', name: 'Singapore', visitors: 900 },
    { code: 'za', name: 'South Africa', visitors: 800 },
    { code: 'kr', name: 'South Korea', visitors: 750 },
    { code: 'ar', name: 'Argentina', visitors: 700 },
    { code: 'cl', name: 'Chile', visitors: 650 },
    { code: 'co', name: 'Colombia', visitors: 600 },
    { code: 'th', name: 'Thailand', visitors: 550 },
    { code: 'my', name: 'Malaysia', visitors: 500 },
    { code: 'ph', name: 'Philippines', visitors: 480 },
    { code: 'id', name: 'Indonesia', visitors: 450 },
    { code: 'vn', name: 'Vietnam', visitors: 420 },
    { code: 'eg', name: 'Egypt', visitors: 400 },
    { code: 'ir', name: 'Iran', visitors: 1200 },
  ]

  const globalMapData: GlobalMapData = {
    countries: hasRealData
      ? globalVisitorDistribution.data.items
          .filter((item) => item.code && item.name && item.visitors)
          .map((item) => ({
            code: item.code.toLowerCase(),
            name: item.name,
            visitors: Number(item.visitors),
          }))
      : fakeCountryData,
  }

  // fake data for metrics - start
  const GoogleIcon = () => (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect width="24" height="24" rx="1.5" fill="#F3F4F6" />
      <path
        d="M19.8759 12.1748C19.8759 11.5273 19.8223 11.0548 19.7063 10.5648H12.1616V13.4873H16.5902C16.5009 14.2136 16.0188 15.3073 14.9473 16.0423L14.9323 16.1401L17.3178 17.9512L17.483 17.9673C19.0009 16.5936 19.8759 14.5723 19.8759 12.1748Z"
        fill="#4285F4"
      />
      <path
        d="M12.161 19.8751C14.3306 19.8751 16.152 19.175 17.4824 17.9675L14.9467 16.0425C14.2681 16.5062 13.3574 16.83 12.161 16.83C10.036 16.83 8.23246 15.4563 7.58954 13.5575L7.4953 13.5653L5.01486 15.4466L4.98242 15.535C6.30383 18.1075 9.01811 19.8751 12.161 19.8751Z"
        fill="#34A853"
      />
      <path
        d="M7.59022 13.5574C7.42058 13.0674 7.32241 12.5423 7.32241 11.9999C7.32241 11.4573 7.42058 10.9323 7.5813 10.4423L7.57681 10.338L5.06527 8.42651L4.9831 8.46482C4.43848 9.53233 4.12598 10.7311 4.12598 11.9999C4.12598 13.2686 4.43848 14.4673 4.9831 15.5348L7.59022 13.5574Z"
        fill="#FBBC05"
      />
      <path
        d="M12.161 7.16998C13.6699 7.16998 14.6878 7.80873 15.2682 8.34251L17.536 6.1725C16.1432 4.90375 14.3306 4.125 12.161 4.125C9.01814 4.125 6.30384 5.89249 4.98242 8.46497L7.58063 10.4425C8.23248 8.54375 10.036 7.16998 12.161 7.16998Z"
        fill="#EB4335"
      />
    </svg>
  )

  const metricsData = [
    {
      label: 'Visitors',
      value: '3,202',
      percentage: '1.2',
      isNegative: true,
      tooltipContent: 'Total number of unique visitors',
    },
    {
      label: 'Views',
      value: '3,940',
      percentage: '1.2',
      isNegative: true,
      icon: <GoogleIcon />,
      tooltipContent: 'Total page views',
    },
    {
      label: 'Exit',
      value: '3,416',
      percentage: '8.3',
      isNegative: false,
      icon: <GoogleIcon />,
      tooltipContent: 'Total exit events',
    },
    {
      label: 'Exit Rate',
      value: '5:56',
      percentage: '1.2',
      isNegative: true,
      icon: <GoogleIcon />,
      tooltipContent: 'Average time users spend on your site before leaving',
    },
    {
      label: 'View',
      value: '86%',
      percentage: '8.3',
      isNegative: false,
      tooltipContent: 'Percentage of page views',
    },
    {
      label: 'Sessions',
      value: '1.30',
      percentage: '1.2',
      isNegative: true,
      icon: <GoogleIcon />,
      tooltipContent: 'Total number of sessions',
    },
    {
      label: 'Average Session Duration',
      value: '45',
      percentage: '1.2',
      isNegative: true,
      icon: <GoogleIcon />,
      tooltipContent: 'Average duration of a user session on your site',
    },
    {
      label: 'Views Per Session',
      value: '$2,050',
      percentage: '8.3',
      isNegative: false,
      tooltipContent: 'Average views per session',
    },
    {
      label: 'Refunds',
      value: '359.1K',
      percentage: '8.3',
      isNegative: false,
      icon: <GoogleIcon />,
      tooltipContent: 'Total refunds processed',
    },
  ]
  // fake data for metrics - end

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
          <Metrics metrics={metricsData} columns={3} />
        </div>

        <div className="col-span-12">
          <LineChart
            title="Traffic Trends"
            data={trafficTrendsData}
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
            items={topCountries.data.items.map((item) => {
              const currentValue = Number(item.value)
              const previousValue = Number(item.previous_value)

              const percentageChange =
                previousValue > 0 ? (((currentValue - previousValue) / previousValue) * 100).toFixed(1) : '0'
              const isNegative = currentValue < previousValue

              return {
                icon: (
                  <img src={`${pluginUrl}public/images/flags/${item.icon || '000'}.svg`} alt={item.label} className="w-4 h-3" />
                ),
                label: item.label,
                value: item.value.toLocaleString(),
                percentage: Math.abs(parseFloat(percentageChange)).toString(),
                isNegative,
                tooltipTitle: 'November 2025',
                tooltipSubtitle: `${__('Previous Data: ', 'wp-statistics')} ${item.previous_value}`,
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
            items={devicesType.data.items.map((item) => {
              const currentValue = Number(item.value)
              const previousValue = Number(item.previous_value)

              const percentageChange =
                previousValue > 0 ? (((currentValue - previousValue) / previousValue) * 100).toFixed(1) : '0'
              const isNegative = currentValue < previousValue

              return {
                icon: (
                  <img src={`${pluginUrl}public/images/device/${item.icon}.svg`} alt={item.label} className="w-4 h-3" />
                ),
                label: item.label,
                value: item.value.toLocaleString(),
                percentage: Math.abs(parseFloat(percentageChange)).toString(),
                isNegative,
                tooltipTitle: 'November 2025',
                tooltipSubtitle: `${__('Previous Data: ', 'wp-statistics')} ${item.previous_value}`,
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
            items={oss.data.items.map((item) => {
              const currentValue = Number(item.value)
              const previousValue = Number(item.previous_value)

              const percentageChange =
                previousValue > 0 ? (((currentValue - previousValue) / previousValue) * 100).toFixed(1) : '0'
              const isNegative = currentValue < previousValue

              return {
                icon: (
                  <img
                    src={`${pluginUrl}public/images/operating-system/${item.icon}.svg`}
                    alt={item.label}
                    className="w-4 h-3"
                  />
                ),
                label: item.label,
                value: item.value.toLocaleString(),
                percentage: Math.abs(parseFloat(percentageChange)).toString(),
                isNegative,
                tooltipTitle: 'November 2025',
                tooltipSubtitle: `${__('Previous Data: ', 'wp-statistics')} ${item.previous_value}`,
              }
            })}
            link={{
              title: __('View Operating Systems', 'wp-statistics'),
              action: () => console.log('View all operating systems'),
            }}
          />
        </div>

        <div className="col-span-12">
          <OverviewTopVisitors />
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
