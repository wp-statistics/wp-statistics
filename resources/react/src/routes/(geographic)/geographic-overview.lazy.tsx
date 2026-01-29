import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { GlobalMap } from '@/components/custom/global-map'
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
  ChartSkeleton,
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
import { getGeographicOverviewQueryOptions } from '@/services/geographic/get-geographic-overview'

const wp = WordPress.getInstance()
const userCountry = wp.getUserCountry() || 'US'
const userCountryName = wp.getUserCountryName() || ''
const pluginUrl = wp.getPluginUrl()

const countryIcon = (item: { country_code?: string; country_name?: string }) => (
  <img
    src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
    alt={item.country_name || ''}
    className="w-4 h-3"
  />
)

const WIDGET_CONFIGS: WidgetConfig[] = [
  { id: 'metrics', label: __('Metrics Overview', 'wp-statistics'), defaultVisible: true },
  { id: 'global-map', label: __('Global Visitor Distribution', 'wp-statistics'), defaultVisible: true },
  { id: 'top-countries', label: __('Top Countries', 'wp-statistics'), defaultVisible: true },
  { id: 'top-cities', label: __('Top Cities', 'wp-statistics'), defaultVisible: true },
  { id: 'european-countries', label: __('Top European Countries', 'wp-statistics'), defaultVisible: true },
  { id: 'us-states', label: __('Top US States', 'wp-statistics'), defaultVisible: true },
  { id: 'visitors-by-continent', label: __('Visitors by Continent', 'wp-statistics'), defaultVisible: true },
  ...(userCountry !== 'US'
    ? [{ id: 'top-regions', label: __('Top Regions', 'wp-statistics'), defaultVisible: true }]
    : []),
]

const METRIC_CONFIGS = pickMetrics('topCountry', 'topRegion', 'topCity')

const OPTIONS_CONFIG: OverviewOptionsConfig = {
  pageId: 'geographic-overview',
  filterGroup: 'geographic',
  widgetConfigs: WIDGET_CONFIGS,
  metricConfigs: METRIC_CONFIGS,
  hideFilters: true,
}

export const Route = createLazyFileRoute('/(geographic)/geographic-overview')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">Error Loading Page</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  return (
    <OverviewOptionsProvider config={OPTIONS_CONFIG}>
      <GeographicOverviewContent />
    </OverviewOptionsProvider>
  )
}

function GeographicOverviewContent() {
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
    ...getGeographicOverviewQueryOptions({
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
  const metricsTopCountry = batchResponse?.data?.items?.metrics_top_country
  const metricsTopRegion = batchResponse?.data?.items?.metrics_top_region
  const metricsTopCity = batchResponse?.data?.items?.metrics_top_city

  const countriesMapData = batchResponse?.data?.items?.countries_map?.data?.rows || []

  const topCountriesData = batchResponse?.data?.items?.top_countries?.data?.rows || []
  const topCountriesTotals = batchResponse?.data?.items?.top_countries?.data?.totals
  const topCitiesData = batchResponse?.data?.items?.top_cities?.data?.rows || []
  const topCitiesTotals = batchResponse?.data?.items?.top_cities?.data?.totals
  const topEuropeanData = batchResponse?.data?.items?.top_european_countries?.data?.rows || []
  const topEuropeanTotals = batchResponse?.data?.items?.top_european_countries?.data?.totals
  const topUsStatesData = batchResponse?.data?.items?.top_us_states?.data?.rows || []
  const topUsStatesTotals = batchResponse?.data?.items?.top_us_states?.data?.totals
  const topRegionsData = batchResponse?.data?.items?.top_regions?.data?.rows || []
  const topRegionsTotals = batchResponse?.data?.items?.top_regions?.data?.totals
  const continentData = batchResponse?.data?.items?.visitors_by_continent?.data?.rows || []
  const continentTotals = batchResponse?.data?.items?.visitors_by_continent?.data?.totals

  // Transform map data
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

  // Build context metrics
  const overviewMetrics = useMemo(() => {
    const topCountryName = metricsTopCountry?.items?.[0]?.country_name
    const topRegionName = metricsTopRegion?.items?.[0]?.region_name
    const topCityName = metricsTopCity?.items?.[0]?.city_name

    const allMetrics = [
      { id: 'top-country', label: __('Top Country', 'wp-statistics'), value: topCountryName || '-' },
      { id: 'top-region', label: __('Top Region', 'wp-statistics'), value: topRegionName || '-' },
      { id: 'top-city', label: __('Top City', 'wp-statistics'), value: topCityName || '-' },
    ]

    return allMetrics.filter((metric) => isMetricVisible(metric.id))
  }, [metricsTopCountry, metricsTopRegion, metricsTopCity, isMetricVisible])

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Geographic Overview', 'wp-statistics')}
        filterGroup="geographic"
        optionsTriggerProps={options.triggerProps}
        showFilterButton={false}
      />

      <OverviewOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-3" currentRoute="geographic-overview" />

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={3} columns={3} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12">
              <PanelSkeleton titleWidth="w-40">
                <ChartSkeleton height={256} showTitle={false} />
              </PanelSkeleton>
            </div>
            {[1, 2, 3, 4, 5, 6].map((i) => (
              <div key={i} className="col-span-12 lg:col-span-6">
                <PanelSkeleton>
                  <BarListSkeleton items={5} showIcon />
                </PanelSkeleton>
              </div>
            ))}
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Context Metrics */}
            {isWidgetVisible('metrics') && overviewMetrics.length > 0 && (
              <div className="col-span-12">
                <Panel>
                  <Metrics metrics={overviewMetrics} />
                </Panel>
              </div>
            )}

            {/* Row 2: Global Map */}
            {isWidgetVisible('global-map') && (
              <div className="col-span-12">
                <GlobalMap
                  data={globalMapData}
                  isLoading={isLoading}
                  dateFrom={apiDateParams.date_from}
                  dateTo={apiDateParams.date_to}
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
            )}

            {/* Row 3: Top Countries | Top Cities */}
            {isWidgetVisible('top-countries') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Countries', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Country', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topCountriesData, {
                    label: (item) => item.country_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topCountriesTotals?.visitors?.current ?? topCountriesTotals?.visitors) || 1,
                    icon: countryIcon,
                    isCompareEnabled,
                    comparisonDateLabel,
                    linkTo: () => '/country/$countryCode',
                    linkParams: (item) => ({ countryCode: item.country_code?.toLowerCase() || '000' }),
                  })}
                  link={{
                    action: () => navigate({ to: '/countries' }),
                  }}
                />
              </div>
            )}

            {isWidgetVisible('top-cities') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top Cities', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('City', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topCitiesData, {
                    label: (item) => item.city_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topCitiesTotals?.visitors?.current ?? topCitiesTotals?.visitors) || 1,
                    icon: countryIcon,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                  link={{
                    action: () => navigate({ to: '/cities' }),
                  }}
                />
              </div>
            )}

            {/* Row 4: European Countries | US States */}
            {isWidgetVisible('european-countries') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top European Countries', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Country', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topEuropeanData, {
                    label: (item) => item.country_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topEuropeanTotals?.visitors?.current ?? topEuropeanTotals?.visitors) || 1,
                    icon: countryIcon,
                    isCompareEnabled,
                    comparisonDateLabel,
                    linkTo: () => '/country/$countryCode',
                    linkParams: (item) => ({ countryCode: item.country_code?.toLowerCase() || '000' }),
                  })}
                  link={{
                    action: () => navigate({ to: '/european-countries' }),
                  }}
                />
              </div>
            )}

            {isWidgetVisible('us-states') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Top US States', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('State', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topUsStatesData, {
                    label: (item) => item.region_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topUsStatesTotals?.visitors?.current ?? topUsStatesTotals?.visitors) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                  link={{
                    action: () => navigate({ to: '/us-states' }),
                  }}
                />
              </div>
            )}

            {/* Row 5: Visitors by Continent | Top Regions */}
            {isWidgetVisible('visitors-by-continent') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={__('Visitors by Continent', 'wp-statistics')}
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Continent', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(continentData, {
                    label: (item) => item.continent_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(continentTotals?.visitors?.current ?? continentTotals?.visitors) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                />
              </div>
            )}

            {userCountry !== 'US' && isWidgetVisible('top-regions') && (
              <div className="col-span-12 lg:col-span-6">
                <HorizontalBarList
                  title={
                    userCountryName
                      ? `${__('Top Regions of', 'wp-statistics')} ${userCountryName}`
                      : __('Top Regions', 'wp-statistics')
                  }
                  showComparison={isCompareEnabled}
                  columnHeaders={{
                    left: __('Region', 'wp-statistics'),
                    right: __('Visitors', 'wp-statistics'),
                  }}
                  items={transformToBarList(topRegionsData, {
                    label: (item) => item.region_name || __('Unknown', 'wp-statistics'),
                    value: (item) => Number(item.visitors) || 0,
                    previousValue: (item) => Number(item.previous?.visitors) || 0,
                    total: Number(topRegionsTotals?.visitors?.current ?? topRegionsTotals?.visitors) || 1,
                    isCompareEnabled,
                    comparisonDateLabel,
                  })}
                  link={{
                    action: () => navigate({ to: '/country-regions' }),
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
