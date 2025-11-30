import { createLazyFileRoute } from '@tanstack/react-router'
import * as React from 'react'

import type { GlobalMapData } from '@/components/custom/global-map'
import { GlobalMap } from '@/components/custom/global-map'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import { WordPress } from '@/lib/wordpress'
import { getVisitorInsightDevicesTypeQueryOptions } from '@/services/visitor-insight/get-devices-type'
import { getVisitorInsightGlobalVisitorDistributionQueryOptions } from '@/services/visitor-insight/get-global-visitor-distribution'
import { getVisitorInsightOSSQueryOptions } from '@/services/visitor-insight/get-oss'
import { getVisitorInsightTopCountriesQueryOptions } from '@/services/visitor-insight/get-top-countries'
import { useSuspenseQuery } from '@tanstack/react-query'
import { __ } from '@wordpress/i18n'
import { OverviewTopVisitors } from './-components/overview/overview-top-visitors'
import { Card, CardHeader, CardTitle } from '@/components/ui/card'
import { getVisitorInsightTrafficTrendsQueryOptions } from '@/services/visitor-insight/get-traffic-trends'

export const Route = createLazyFileRoute('/(visitor-insights)/visitors-overview')({
  component: RouteComponent,
})

function RouteComponent() {
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  const [timeframe, setTimeframe] = React.useState<'daily' | 'weekly' | 'monthly'>('daily')

  const {
    data: { data: topCountries },
  } = useSuspenseQuery(getVisitorInsightTopCountriesQueryOptions())

  const {
    data: { data: devicesType },
  } = useSuspenseQuery(getVisitorInsightDevicesTypeQueryOptions())

  const {
    data: { data: oss },
  } = useSuspenseQuery(getVisitorInsightOSSQueryOptions())

  const {
    data: { data: globalVisitorDistribution },
  } = useSuspenseQuery(getVisitorInsightGlobalVisitorDistributionQueryOptions())

  const {
    data: { data: trafficTrends },
  } = useSuspenseQuery(getVisitorInsightTrafficTrendsQueryOptions({ range: timeframe }))

  const trafficTrendsData = trafficTrends.data.items

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

  const globalMapData: GlobalMapData = {
    countries: globalVisitorDistribution.data.items
      .filter((item) => item.code && item.name && item.visitors)
      .map((item) => ({
        code: item.code.toLowerCase(),
        name: item.name,
        visitors: Number(item.visitors),
      })),
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
    <div className="p-2 grid gap-6">
      <h1 className="text-2xl font-medium text-neutral-700">Visitor insights</h1>
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
                  <img src={`${pluginUrl}public/images/flags/${item.icon}.svg`} alt={item.label} className="w-4 h-3" />
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
          />
        </div>
      </div>
    </div>
  )
}
