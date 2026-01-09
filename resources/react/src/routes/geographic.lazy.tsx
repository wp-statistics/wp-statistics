import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useMemo } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { GlobalMap } from '@/components/custom/global-map'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { Metrics } from '@/components/custom/metrics'
import { Panel } from '@/components/ui/panel'
import { NoticeContainer } from '@/components/ui/notice-container'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { calcSharePercentage } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getGeographicOverviewQueryOptions } from '@/services/geographic/get-geographic-overview'

export const Route = createLazyFileRoute('/geographic')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">Error Loading Page</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    setDateRange,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const userCountry = wp.getUserCountry() || 'US'
  const userCountryName = wp.getUserCountryName() || 'United States'

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  const { data: batchResponse, isLoading } = useQuery({
    ...getGeographicOverviewQueryOptions({
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
  const metricsTopCountry = batchResponse?.data?.items?.metrics_top_country
  const metricsTopRegion = batchResponse?.data?.items?.metrics_top_region
  const metricsTopCity = batchResponse?.data?.items?.metrics_top_city
  const countriesMapData = batchResponse?.data?.items?.countries_map?.data?.rows || []
  const topCountriesData = batchResponse?.data?.items?.top_countries?.data?.rows || []
  const topCountriesTotals = batchResponse?.data?.items?.top_countries?.data?.totals
  const topCitiesData = batchResponse?.data?.items?.top_cities?.data?.rows || []
  const topCitiesTotals = batchResponse?.data?.items?.top_cities?.data?.totals
  const topEuropeanCountriesData = batchResponse?.data?.items?.top_european_countries?.data?.rows || []
  const topEuropeanCountriesTotals = batchResponse?.data?.items?.top_european_countries?.data?.totals
  const topRegionsData = batchResponse?.data?.items?.top_regions?.data?.rows || []
  const topRegionsTotals = batchResponse?.data?.items?.top_regions?.data?.totals
  const topUsStatesData = batchResponse?.data?.items?.top_us_states?.data?.rows || []
  const topUsStatesTotals = batchResponse?.data?.items?.top_us_states?.data?.totals
  const visitorsByContinentData = batchResponse?.data?.items?.visitors_by_continent?.data?.rows || []
  const visitorsByContinentTotals = batchResponse?.data?.items?.visitors_by_continent?.data?.totals

  // Transform countries map data for GlobalMap component
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

  const calcPercentage = usePercentageCalc()

  // Build metrics for the top row
  const geographicMetrics = useMemo(() => {
    const topCountryName = metricsTopCountry?.items?.[0]?.country_name
    const topCountryCode = metricsTopCountry?.items?.[0]?.country_code
    const topRegionName = metricsTopRegion?.items?.[0]?.region_name
    const topCityName = metricsTopCity?.items?.[0]?.city_name

    return [
      {
        label: __('Top Country', 'wp-statistics'),
        value: topCountryName || '-',
        icon: topCountryCode ? (
          <img
            src={`${pluginUrl}public/images/flags/${topCountryCode.toLowerCase()}.svg`}
            alt={topCountryName || ''}
            className="w-5 h-4"
          />
        ) : undefined,
        tooltipContent: __('Country with the most visitors', 'wp-statistics'),
      },
      {
        label: __('Top Region', 'wp-statistics'),
        value: topRegionName || '-',
        tooltipContent: __('Region with the most visitors', 'wp-statistics'),
      },
      {
        label: __('Top City', 'wp-statistics'),
        value: topCityName || '-',
        tooltipContent: __('City with the most visitors', 'wp-statistics'),
      },
    ]
  }, [metricsTopCountry, metricsTopRegion, metricsTopCity, pluginUrl])

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
        <h1 className="text-xl font-semibold text-neutral-800">{__('Geographic', 'wp-statistics')}</h1>
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

      <div className="p-2">
        <NoticeContainer className="mb-2" currentRoute="geographic" />
        {showSkeleton ? (
          <div className="grid gap-2 grid-cols-12">
            {/* Metrics skeleton */}
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={3} columns={3} />
              </PanelSkeleton>
            </div>
            {/* Global Map skeleton */}
            <div className="col-span-12">
              <PanelSkeleton titleWidth="w-40">
                <ChartSkeleton height={256} showTitle={false} />
              </PanelSkeleton>
            </div>
            {/* Row 3 skeleton */}
            {[1, 2, 3].map((i) => (
              <div key={i} className="col-span-12 lg:col-span-4">
                <PanelSkeleton>
                  <BarListSkeleton items={5} showIcon />
                </PanelSkeleton>
              </div>
            ))}
            {/* Row 4 skeleton */}
            {[1, 2, 3].map((i) => (
              <div key={`r4-${i}`} className="col-span-12 lg:col-span-4">
                <PanelSkeleton>
                  <BarListSkeleton items={5} />
                </PanelSkeleton>
              </div>
            ))}
          </div>
        ) : (
          <div className="grid gap-2 grid-cols-12">
            {/* Row 1: Geographic Metrics */}
            <div className="col-span-12">
              <Panel>
                <Metrics metrics={geographicMetrics} columns={3} />
              </Panel>
            </div>

            {/* Row 2: Global Visitor Distribution Map */}
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

            {/* Row 3: Top Countries, Top Cities, Top European Countries */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Countries', 'wp-statistics')}
                items={(() => {
                  const totalVisitors =
                    Number(topCountriesTotals?.visitors?.current ?? topCountriesTotals?.visitors) || 1
                  return topCountriesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)

                    return {
                      icon: (
                        <img
                          src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
                          alt={item.country_name || ''}
                          className="w-4 h-3"
                        />
                      ),
                      label: item.country_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.country_name || '',
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Countries', 'wp-statistics'),
                  href: '#/countries',
                }}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Cities', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topCitiesTotals?.visitors?.current ?? topCitiesTotals?.visitors) || 1
                  return topCitiesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)

                    return {
                      label: item.city_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.city_name || '',
                      tooltipSubtitle: `${item.country_name || ''} • ${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Cities', 'wp-statistics'),
                  href: '#/cities',
                }}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top European Countries', 'wp-statistics')}
                items={(() => {
                  const totalVisitors =
                    Number(topEuropeanCountriesTotals?.visitors?.current ?? topEuropeanCountriesTotals?.visitors) || 1
                  return topEuropeanCountriesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)

                    return {
                      icon: (
                        <img
                          src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
                          alt={item.country_name || ''}
                          className="w-4 h-3"
                        />
                      ),
                      label: item.country_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.country_name || '',
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View European Countries', 'wp-statistics'),
                  href: '#/countries?filter_country=EU',
                }}
              />
            </div>

            {/* Row 4: Top Regions, Top US States, Visitors by Continent */}
            {/* Only show Top Regions if user country is not US */}
            {userCountry !== 'US' && (
              <div className="col-span-12 lg:col-span-4">
                <HorizontalBarList
                  title={`${__('Top Regions of', 'wp-statistics')} ${userCountryName}`}
                  items={(() => {
                    const totalVisitors =
                      Number(topRegionsTotals?.visitors?.current ?? topRegionsTotals?.visitors) || 1
                    return topRegionsData.map((item) => {
                      const currentValue = Number(item.visitors) || 0
                      const previousValue = Number(item.previous?.visitors) || 0
                      const { percentage, isNegative } = calcPercentage(currentValue, previousValue)

                      return {
                        label: item.region_name || __('Unknown', 'wp-statistics'),
                        value: currentValue,
                        percentage,
                        fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                        isNegative,
                        tooltipTitle: item.region_name || '',
                        tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                      }
                    })
                  })()}
                  link={{
                    title: __('View Regions', 'wp-statistics'),
                    href: `#/regions?filter_country=${userCountry}`,
                  }}
                />
              </div>
            )}

            <div className={`col-span-12 lg:col-span-4 ${userCountry === 'US' ? 'lg:col-start-1' : ''}`}>
              <HorizontalBarList
                title={__('Top US States', 'wp-statistics')}
                items={(() => {
                  const totalVisitors =
                    Number(topUsStatesTotals?.visitors?.current ?? topUsStatesTotals?.visitors) || 1
                  return topUsStatesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)

                    return {
                      label: item.region_name || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.region_name || '',
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View US States', 'wp-statistics'),
                  href: '#/regions?filter_country=US',
                }}
              />
            </div>

            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Visitors by Continent', 'wp-statistics')}
                items={(() => {
                  const totalVisitors =
                    Number(visitorsByContinentTotals?.visitors?.current ?? visitorsByContinentTotals?.visitors) || 1
                  return visitorsByContinentData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)

                    return {
                      label: item.continent_name || item.continent || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.continent_name || item.continent || '',
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
              />
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
