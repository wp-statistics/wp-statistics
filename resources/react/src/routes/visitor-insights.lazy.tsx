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

  return (
    <div className="p-2 grid gap-6">
      <h1 className="text-2xl font-medium text-neutral-700">Visitor insights</h1>
      <div className="grid gap-3 grid-cols-12">
        <div className="col-span-12">{__('Statistics Section', 'wp-statistics')}</div>

        <Card className="col-span-12">
          <CardHeader>
            <CardTitle>Traffic Trends</CardTitle>
          </CardHeader>
        </Card>

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

        <Card className="col-span-6">
          <CardHeader>
            <CardTitle>Global Visitor Distribution</CardTitle>
          </CardHeader>
        </Card>
      </div>
    </div>
  )
}
