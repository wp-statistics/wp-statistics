import { createLazyFileRoute } from '@tanstack/react-router'
import * as React from 'react'

import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { ChevronRight } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { __ } from '@wordpress/i18n'
import { useSuspenseQuery } from '@tanstack/react-query'
import { getVisitorInsightTopCountriesQueryOptions } from '@/services/visitor-insight/get-top-countries'
import { getVisitorInsightDevicesTypeQueryOptions } from '@/services/visitor-insight/get-devices-type'
import { WordPress } from '@/lib/wordpress'
import { getVisitorInsightOSSQueryOptions } from '@/services/visitor-insight/get-oss'
import { LineChart } from '@/components/custom/line-chart'
import { GlobalMap } from '@/components/custom/global-map'
import type { GlobalMapData } from '@/components/custom/global-map'
import { getVisitorInsightGlobalVisitorDistributionQueryOptions } from '@/services/visitor-insight/get-global-visitor-distribution'

export const Route = createLazyFileRoute('/visitor-insights')({
  component: RouteComponent,
})

function RouteComponent() {
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

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

  const [timeframe, setTimeframe] = React.useState<'Daily' | 'Weekly' | 'Monthly'>('Daily')

  // Generate fake data based on timeframe
  const generateTrafficData = (timeframe: 'Daily' | 'Weekly' | 'Monthly') => {
    if (timeframe === 'Daily') {
      // Daily data: Oct 27 - Nov 25 (30 days)
      return Array.from({ length: 30 }, (_, i) => {
        const date = new Date(2024, 9, 27 + i) // Oct 27 = month 9, day 27
        const dayOfWeek = date.getDay()

        // Weekend dip pattern (lower traffic on weekends)
        const weekendFactor = dayOfWeek === 0 || dayOfWeek === 6 ? 0.7 : 1.0

        // Base values with gradual upward trend
        const trendFactor = 1 + i / 100

        // Add some randomness and weekly patterns
        const noise = Math.random() * 0.4 - 0.2
        const weeklyPattern = Math.sin(i / 3.5) * 0.3

        const visitorsBase = 2.2 + weeklyPattern + noise
        const visitorsPrevBase = 1.8 + weeklyPattern * 0.8 + noise * 0.8
        const viewsBase = 3.2 + weeklyPattern * 1.2 + noise
        const viewsPrevBase = 2.8 + weeklyPattern + noise * 0.8

        return {
          date: date.toISOString(),
          visitors: Math.round(visitorsBase * weekendFactor * trendFactor * 10) / 10,
          visitorsPrevious: Math.round(visitorsPrevBase * weekendFactor * 0.95 * 10) / 10,
          views: Math.round(viewsBase * weekendFactor * trendFactor * 10) / 10,
          viewsPrevious: Math.round(viewsPrevBase * weekendFactor * 0.9 * 10) / 10,
        }
      })
    } else if (timeframe === 'Weekly') {
      // Weekly data: 5 weeks from Oct 27 to Nov 25
      const weeks = [
        { start: new Date(2024, 9, 27), visitors: 14.5, views: 21.3 }, // Oct 27 - Nov 2
        { start: new Date(2024, 10, 3), visitors: 15.8, views: 23.1 }, // Nov 3 - Nov 9
        { start: new Date(2024, 10, 10), visitors: 16.2, views: 24.5 }, // Nov 10 - Nov 16
        { start: new Date(2024, 10, 17), visitors: 14.9, views: 22.8 }, // Nov 17 - Nov 23
        { start: new Date(2024, 10, 24), visitors: 15.4, views: 23.9 }, // Nov 24 - Nov 25
      ]

      return weeks.map((week) => ({
        date: week.start.toISOString(),
        visitors: week.visitors,
        visitorsPrevious: Math.round((week.visitors * 0.85 + Math.random() * 0.5) * 10) / 10,
        views: week.views,
        viewsPrevious: Math.round((week.views * 0.87 + Math.random() * 0.8) * 10) / 10,
      }))
    } else {
      // Monthly data: October and November
      return [
        {
          date: new Date(2024, 9, 1).toISOString(), // October
          visitors: 58.3,
          visitorsPrevious: 49.7,
          views: 89.6,
          viewsPrevious: 75.2,
        },
        {
          date: new Date(2024, 10, 1).toISOString(), // November
          visitors: 76.8,
          visitorsPrevious: 58.3,
          views: 115.6,
          viewsPrevious: 89.6,
        },
      ]
    }
  }

  const trafficTrendsData = generateTrafficData(timeframe)

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

  return (
    <div className="p-2 grid gap-6">
      <h1 className="text-2xl font-medium text-neutral-700">Visitor insights</h1>
      <div className="grid gap-3 grid-cols-12">
        <div className="col-span-12">{__('Statistics Section', 'wp-statistics')}</div>

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

        <Card className="col-span-12">
          <CardHeader>
            <CardTitle>Top Visitors</CardTitle>
          </CardHeader>
          <CardContent></CardContent>
          <CardFooter>
            <Button
              className="ml-auto gap-1 items-center font-normal hover:no-underline text-xs text-neutral-600"
              onClick={() => {}}
              variant="link"
            >
              {__('View Visitors')}
              <ChevronRight className="w-3 h-4 ms-0" />
            </Button>
          </CardFooter>
        </Card>

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
