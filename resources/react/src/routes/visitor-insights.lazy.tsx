import { createLazyFileRoute } from '@tanstack/react-router'

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

  // Generate fake data for Traffic Trends (April 1-30)
  const trafficTrendsData = Array.from({ length: 30 }, (_, i) => {
    const date = new Date(2024, 3, i + 1) // April 2024
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

  const trafficTrendsMetrics = [
    {
      key: 'visitors',
      label: 'Visitors',
      color: 'var(--chart-1)',
      enabled: true,
      value: '2.3k',
      previousValue: '1.8k',
    },
    {
      key: 'views',
      label: 'Views',
      color: 'var(--chart-5)',
      enabled: true,
      value: '3.4k',
      previousValue: '2.9k',
    },
  ]

  // Global Visitor Distribution data
  const globalMapData: GlobalMapData = {
    countries: [
      { code: 'US', name: 'United States', flag: '🇺🇸', visitors: 25000 },
      { code: 'FR', name: 'France', flag: '🇫🇷', visitors: 23000 },
      { code: 'GB', name: 'United Kingdom', flag: '🇬🇧', visitors: 18000 },
      { code: 'DE', name: 'Germany', flag: '🇩🇪', visitors: 15000 },
      { code: 'CA', name: 'Canada', flag: '🇨🇦', visitors: 12000 },
      { code: 'AU', name: 'Australia', flag: '🇦🇺', visitors: 10000 },
      { code: 'JP', name: 'Japan', flag: '🇯🇵', visitors: 9000 },
      { code: 'IN', name: 'India', flag: '🇮🇳', visitors: 8000 },
      { code: 'BR', name: 'Brazil', flag: '🇧🇷', visitors: 7000 },
      { code: 'IT', name: 'Italy', flag: '🇮🇹', visitors: 6500 },
      { code: 'ES', name: 'Spain', flag: '🇪🇸', visitors: 6000 },
      { code: 'MX', name: 'Mexico', flag: '🇲🇽', visitors: 5500 },
      { code: 'NL', name: 'Netherlands', flag: '🇳🇱', visitors: 5000 },
      { code: 'SE', name: 'Sweden', flag: '🇸🇪', visitors: 4500 },
      { code: 'CH', name: 'Switzerland', flag: '🇨🇭', visitors: 4000 },
      { code: 'BE', name: 'Belgium', flag: '🇧🇪', visitors: 3500 },
      { code: 'PL', name: 'Poland', flag: '🇵🇱', visitors: 3000 },
      { code: 'AT', name: 'Austria', flag: '🇦🇹', visitors: 2500 },
      { code: 'NO', name: 'Norway', flag: '🇳🇴', visitors: 2000 },
      { code: 'DK', name: 'Denmark', flag: '🇩🇰', visitors: 1800 },
    ],
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
            timeframe="Daily"
            onTimeframeChange={(timeframe) => console.log('Timeframe changed:', timeframe)}
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
            showTimePeriod={true}
            timePeriod="Last 30 days"
            onTimePeriodChange={(period) => console.log('Time period changed:', period)}
            title={__('Global Visitor Distribution', 'wp-statistics')}
          />
        </div>
      </div>
    </div>
  )
}
