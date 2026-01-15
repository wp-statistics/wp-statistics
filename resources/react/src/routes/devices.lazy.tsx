import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo } from 'react'

import { AddonPromo } from '@/components/custom/addon-promo'
import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { Metrics } from '@/components/custom/metrics'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { WordPress } from '@/lib/wordpress'
import { getDevicesOverviewQueryOptions } from '@/services/devices/get-devices-overview'

export const Route = createLazyFileRoute('/devices')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">Error Loading Page</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  const { dateFrom, dateTo, compareDateFrom, compareDateTo, period, setDateRange, isInitialized, apiDateParams, isCompareEnabled } =
    useGlobalFilters()

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const isPremium = wp.getIsPremium()

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  const { data: batchResponse, isLoading } = useQuery({
    ...getDevicesOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const showSkeleton = isLoading && !batchResponse

  // Extract data from batch response
  const metricsTopBrowser = batchResponse?.data?.items?.metrics_top_browser
  const metricsTopOs = batchResponse?.data?.items?.metrics_top_os
  const metricsTopDevice = batchResponse?.data?.items?.metrics_top_device
  const metricsTopResolution = batchResponse?.data?.items?.metrics_top_resolution
  const topBrowsersData = batchResponse?.data?.items?.top_browsers?.data?.rows || []
  const topBrowsersTotals = batchResponse?.data?.items?.top_browsers?.data?.totals
  const topOperatingSystemsData = batchResponse?.data?.items?.top_operating_systems?.data?.rows || []
  const topOperatingSystemsTotals = batchResponse?.data?.items?.top_operating_systems?.data?.totals
  const topDeviceCategoriesData = batchResponse?.data?.items?.top_device_categories?.data?.rows || []
  const topDeviceCategoriesTotals = batchResponse?.data?.items?.top_device_categories?.data?.totals
  const topScreenResolutionsData = batchResponse?.data?.items?.top_screen_resolutions?.data?.rows || []
  const topScreenResolutionsTotals = batchResponse?.data?.items?.top_screen_resolutions?.data?.totals

  const { label: comparisonDateLabel } = useComparisonDateLabel()

  // Build metrics for the top row
  const deviceMetrics = useMemo(() => {
    const topBrowserName = metricsTopBrowser?.items?.[0]?.browser_name as string | undefined
    const topOsName = metricsTopOs?.items?.[0]?.os_name as string | undefined
    const topDeviceName = metricsTopDevice?.items?.[0]?.device_type_name as string | undefined
    const topResolution = metricsTopResolution?.items?.[0]?.screen_resolution as string | undefined

    // Format browser icon name
    const browserIconName = (topBrowserName || 'unknown').toLowerCase().replace(/\s+/g, '_')
    // Format OS icon name
    const osIconName = (topOsName || 'unknown').toLowerCase().replace(/\s+/g, '_')
    // Format device icon name
    const deviceIconName = (topDeviceName || 'desktop').toLowerCase()

    return [
      {
        label: __('Top Browser', 'wp-statistics'),
        value: topBrowserName || '-',
        icon: topBrowserName ? (
          <img
            src={`${pluginUrl}public/images/browser/${browserIconName}.svg`}
            alt={topBrowserName}
            className="w-5 h-5"
          />
        ) : undefined,
        tooltipContent: __('Browser with the most visitors', 'wp-statistics'),
      },
      {
        label: __('Top Operating System', 'wp-statistics'),
        value: topOsName || '-',
        icon: topOsName ? (
          <img
            src={`${pluginUrl}public/images/operating-system/${osIconName}.svg`}
            alt={topOsName}
            className="w-5 h-5"
          />
        ) : undefined,
        tooltipContent: __('Operating system with the most visitors', 'wp-statistics'),
      },
      {
        label: __('Top Device Category', 'wp-statistics'),
        value: topDeviceName || '-',
        icon: topDeviceName ? (
          <img src={`${pluginUrl}public/images/device/${deviceIconName}.svg`} alt={topDeviceName} className="w-5 h-5" />
        ) : undefined,
        tooltipContent: __('Device category with the most visitors', 'wp-statistics'),
      },
      {
        label: __('Top Resolution', 'wp-statistics'),
        value: topResolution || '-',
        tooltipContent: __('Screen resolution with the most visitors', 'wp-statistics'),
      },
    ]
  }, [metricsTopBrowser, metricsTopOs, metricsTopDevice, metricsTopResolution, pluginUrl])

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Devices', 'wp-statistics')}</h1>
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

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="devices" />
        {showSkeleton ? (
          <div className="grid gap-3 grid-cols-12">
            {/* Metrics skeleton */}
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={4} columns={4} />
              </PanelSkeleton>
            </div>
            {/* Row 2 skeleton */}
            {[1, 2].map((i) => (
              <div key={i} className="col-span-12 lg:col-span-6">
                <PanelSkeleton>
                  <BarListSkeleton items={5} showIcon />
                </PanelSkeleton>
              </div>
            ))}
            {/* Row 3 skeleton */}
            {[1, 2].map((i) => (
              <div key={`r3-${i}`} className="col-span-12 lg:col-span-6">
                <PanelSkeleton>
                  <BarListSkeleton items={5} showIcon />
                </PanelSkeleton>
              </div>
            ))}
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Device Metrics */}
            <div className="col-span-12">
              <Panel>
                <Metrics metrics={deviceMetrics} columns={4} />
              </Panel>
            </div>

            {/* Row 2: Top Browsers, Top Operating Systems */}
            <div className="col-span-12 lg:col-span-6">
              <HorizontalBarList
                title={__('Top Browsers', 'wp-statistics')}
                showComparison={isCompareEnabled}
                items={transformToBarList(topBrowsersData, {
                  label: (item) => item.browser_name || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topBrowsersTotals?.visitors?.current ?? topBrowsersTotals?.visitors) || 1,
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/browser/${(item.browser_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
                      alt={item.browser_name || ''}
                      className="w-4 h-4"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
                link={{
                  href: '#/browsers',
                }}
              />
            </div>

            <div className="col-span-12 lg:col-span-6">
              <HorizontalBarList
                title={__('Top Operating Systems', 'wp-statistics')}
                showComparison={isCompareEnabled}
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
                link={{
                  href: '#/operating-systems',
                }}
              />
            </div>

            {/* Row 3: Top Device Categories, Top Screen Resolutions */}
            <div className={`col-span-12 ${isPremium ? 'lg:col-span-6' : ''}`}>
              <HorizontalBarList
                title={__('Top Device Categories', 'wp-statistics')}
                showComparison={isCompareEnabled}
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
                link={{
                  href: '#/device-categories',
                }}
              />
            </div>

            {isPremium ? (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Screen Resolutions', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  items={transformToBarList(topScreenResolutionsData, {
                    label: (item) => item.screen_resolution || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topScreenResolutionsTotals?.visitors?.current ?? topScreenResolutionsTotals?.visitors) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                  link={{
                    href: '#/screen-resolutions',
                  }}
                />
              </div>
            ) : (
              <div className="col-span-12 lg:col-span-6">
                <AddonPromo
                  title={__('Top Screen Resolutions', 'wp-statistics')}
                  description={__(
                    'Track the screen resolutions your visitors use to optimize your design for the most common display sizes.',
                    'wp-statistics'
                  )}
                  addonSlug="wp-statistics-data-plus"
                  addonName={__('Data Plus', 'wp-statistics')}
                />
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  )
}
