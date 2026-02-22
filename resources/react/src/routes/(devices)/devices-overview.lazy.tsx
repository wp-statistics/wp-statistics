import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { Metrics } from '@/components/custom/metrics'
import {
  type OverviewOptionsConfig,
  OverviewOptionsDrawer,
  OverviewOptionsProvider,
  useOverviewOptions,
} from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import {
  BarListSkeleton,
  MetricsSkeleton,
  PanelSkeleton,
} from '@/components/ui/skeletons'
import { pickMetrics } from '@/constants/metric-definitions'
import { type WidgetConfig } from '@/contexts/page-options-context'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePageOptions } from '@/hooks/use-page-options'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { WordPress } from '@/lib/wordpress'
import { getDevicesOverviewQueryOptions } from '@/services/devices/get-devices-overview'

const wp = WordPress.getInstance()
const pluginUrl = wp.getPluginUrl()

// Check premium at module level (same data source as usePremiumFeature hook)
const hasScreenResolutions = wp.getData('premium')?.unlockedFeatures?.['screen-resolutions'] === true

const browserIcon = (item: { browser_name?: string }) => {
  const slug = (item.browser_name || 'unknown').toLowerCase().replace(/\s+/g, '_')
  return (
    <img
      src={`${pluginUrl}public/images/browser/${slug}.svg`}
      alt={item.browser_name || ''}
      className="h-4 w-4"
    />
  )
}

const osIcon = (item: { os_name?: string }) => {
  const slug = (item.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_')
  return (
    <img
      src={`${pluginUrl}public/images/operating-system/${slug}.svg`}
      alt={item.os_name || ''}
      className="h-4 w-4"
    />
  )
}

const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'metrics', label: __('Metrics Overview', 'wp-statistics'), defaultVisible: true },
  { id: 'top-browsers', label: __('Top Browsers', 'wp-statistics'), defaultVisible: true },
  { id: 'top-operating-systems', label: __('Top Operating Systems', 'wp-statistics'), defaultVisible: true },
  { id: 'top-device-categories', label: __('Top Device Categories', 'wp-statistics'), defaultVisible: true },
  ...(hasScreenResolutions
    ? [{ id: 'top-screen-resolutions', label: __('Top Screen Resolutions', 'wp-statistics'), defaultVisible: true }]
    : []),
]

const METRIC_CONFIGS = hasScreenResolutions
  ? pickMetrics('topBrowser', 'topOperatingSystem', 'topDeviceCategory', 'topResolution')
  : pickMetrics('topBrowser', 'topOperatingSystem', 'topDeviceCategory')

const OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'devices-overview',
  filterGroup: 'devices',
  widgetConfigs: WIDGET_CONFIGS,
  metricConfigs: METRIC_CONFIGS,
  hideFilters: true,
}

const METRIC_COUNT = hasScreenResolutions ? 4 : 3
const SKELETON_WIDGET_COUNT = hasScreenResolutions ? 4 : 3

export const Route = createLazyFileRoute('/(devices)/devices-overview')({
  component: RouteComponent,
})

function RouteComponent() {
  return (
    <OverviewOptionsProvider config={OPTIONS_CONFIG}>
      <DevicesOverviewContent />
    </OverviewOptionsProvider>
  )
}

function DevicesOverviewContent() {
  const {
    filters: appliedFilters,
    isInitialized,
    isCompareEnabled,
    apiDateParams,
  } = useGlobalFilters()

  const { isWidgetVisible, isMetricVisible } = usePageOptions()
  const options = useOverviewOptions(OPTIONS_CONFIG)
  const navigate = useNavigate()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getDevicesOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      filters: appliedFilters || [],
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const showSkeleton = isLoading && !batchResponse
  const showFullPageLoading = isFetching && !isLoading

  // Extract data
  const metricsTopBrowser = batchResponse?.data?.items?.metrics_top_browser
  const metricsTopOs = batchResponse?.data?.items?.metrics_top_os
  const metricsTopDevice = batchResponse?.data?.items?.metrics_top_device

  const browsersData = batchResponse?.data?.items?.top_browsers?.data?.rows || []
  const browsersTotals = batchResponse?.data?.items?.top_browsers?.data?.totals
  const osData = batchResponse?.data?.items?.top_operating_systems?.data?.rows || []
  const osTotals = batchResponse?.data?.items?.top_operating_systems?.data?.totals
  const deviceCategoriesData = batchResponse?.data?.items?.top_device_categories?.data?.rows || []
  const deviceCategoriesTotals = batchResponse?.data?.items?.top_device_categories?.data?.totals

  // Build metrics
  const overviewMetrics = useMemo(() => {
    const allMetrics = [
      { id: 'top-browser', label: __('Top Browser', 'wp-statistics'), value: metricsTopBrowser?.items?.[0]?.browser_name || '-' },
      { id: 'top-operating-system', label: __('Top Operating System', 'wp-statistics'), value: metricsTopOs?.items?.[0]?.os_name || '-' },
      { id: 'top-device-category', label: __('Top Device Category', 'wp-statistics'), value: metricsTopDevice?.items?.[0]?.device_type_name || '-' },
    ]

    if (hasScreenResolutions) {
      const metricsTopResolution = batchResponse?.data?.items?.metrics_top_resolution
      allMetrics.push({ id: 'top-resolution', label: __('Top Resolution', 'wp-statistics'), value: metricsTopResolution?.items?.[0]?.screen_resolution || '-' })
    }

    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [metricsTopBrowser, metricsTopOs, metricsTopDevice, batchResponse, isMetricVisible])

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Devices Overview', 'wp-statistics')}
        filterGroup="devices"
        optionsTriggerProps={options.triggerProps}
        showFilterButton={false}
      />

      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-3" currentRoute="devices-overview" />

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={METRIC_COUNT} columns={METRIC_COUNT} />
              </PanelSkeleton>
            </div>
            {[...Array(SKELETON_WIDGET_COUNT)].map((_, i) => (
              <div key={i} className="col-span-12 lg:col-span-6">
                <PanelSkeleton>
                  <BarListSkeleton items={5} showIcon={i < 2} />
                </PanelSkeleton>
              </div>
            ))}
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Metrics */}
            {isWidgetVisible('metrics') && overviewMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={overviewMetrics} />
                </Panel>
              </div>
            )}

            {/* Row 2: Top Browsers | Top Operating Systems */}
            {isWidgetVisible('top-browsers') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Browsers', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Browser', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(browsersData, {
                    label: (item) => item.browser_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(browsersTotals?.visitors?.current ?? browsersTotals?.visitors) || 1,
                    icon: browserIcon,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                  link={{
                    action: () => navigate({ to: '/browsers' }),
                  }}
                />
              </div>
            )}

            {isWidgetVisible('top-operating-systems') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Operating Systems', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Operating System', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(osData, {
                    label: (item) => item.os_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(osTotals?.visitors?.current ?? osTotals?.visitors) || 1,
                    icon: osIcon,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                  link={{
                    action: () => navigate({ to: '/operating-systems' }),
                  }}
                />
              </div>
            )}

            {/* Row 3: Device Categories | Screen Resolutions */}
            {isWidgetVisible('top-device-categories') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Device Categories', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Device Category', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(deviceCategoriesData, {
                    label: (item) => item.device_type_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(deviceCategoriesTotals?.visitors?.current ?? deviceCategoriesTotals?.visitors) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                  link={{
                    action: () => navigate({ to: '/device-categories' }),
                  }}
                />
              </div>
            )}

            {hasScreenResolutions && isWidgetVisible('top-screen-resolutions') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Screen Resolutions', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Resolution', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(
                    batchResponse?.data?.items?.top_screen_resolutions?.data?.rows || [],
                    {
                      label: (item) => item.screen_resolution || __('Unknown', 'wp-statistics'),
                      value: (item) => Number(item.visitors) || 0,
                      previousValue: (item) => Number(item.previous?.visitors) || 0,
                      total: Number(
                        batchResponse?.data?.items?.top_screen_resolutions?.data?.totals?.visitors?.current
                          ?? batchResponse?.data?.items?.top_screen_resolutions?.data?.totals?.visitors
                      ) || 1,
                      isCompareEnabled,
                      comparisonDateLabel,
                    },
                  )}
                  link={{
                    action: () => navigate({ to: '/screen-resolutions' }),
                  }}
                />
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  )
}
