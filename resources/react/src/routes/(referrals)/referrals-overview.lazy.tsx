import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import { Panel } from '@/components/ui/panel'
import { NoticeContainer } from '@/components/ui/notice-container'
import {
  BarListSkeleton,
  ChartSkeleton,
  MetricsSkeleton,
  PanelSkeleton,
} from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { calcSharePercentage, formatCompactNumber, formatDecimal } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getReferralsOverviewQueryOptions } from '@/services/referral/get-referrals-overview'

export const Route = createLazyFileRoute('/(referrals)/referrals-overview')({
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
    filters: appliedFilters,
    setDateRange,
    applyFilters: handleApplyFilters,
    removeFilter: handleRemoveFilter,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('referrals') as FilterField[]
  }, [wp])

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevFiltersRef = useRef<string>(JSON.stringify(appliedFilters))
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  useEffect(() => {
    const currentFilters = JSON.stringify(appliedFilters)
    const filtersChanged = currentFilters !== prevFiltersRef.current
    const dateRangeChanged = dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current

    if (filtersChanged || dateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    prevFiltersRef.current = currentFilters
    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [appliedFilters, dateFrom, dateTo])

  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange; period?: string }) => {
      setDateRange(values.range, values.rangeCompare, values.period)
    },
    [setDateRange]
  )

  const {
    data: batchResponse,
    isLoading,
    isFetching,
  } = useQuery({
    ...getReferralsOverviewQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      timeframe,
      filters: appliedFilters || [],
    }),
    retry: false,
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  const showSkeleton = isLoading && !batchResponse
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Extract data from batch response
  const metricsResponse = batchResponse?.data?.items?.metrics
  const metricsTopReferrer = batchResponse?.data?.items?.metrics_top_referrer
  const metricsTopCountry = batchResponse?.data?.items?.metrics_top_country
  const metricsTopBrowser = batchResponse?.data?.items?.metrics_top_browser
  const metricsTopSearchEngine = batchResponse?.data?.items?.metrics_top_search_engine
  const metricsTopSocial = batchResponse?.data?.items?.metrics_top_social
  const metricsTopEntryPage = batchResponse?.data?.items?.metrics_top_entry_page
  const trafficTrendsResponse = batchResponse?.data?.items?.traffic_trends

  // Table data
  const topReferrersData = batchResponse?.data?.items?.top_referrers?.data?.rows || []
  const topReferrersTotals = batchResponse?.data?.items?.top_referrers?.data?.totals
  const topCountriesData = batchResponse?.data?.items?.top_countries?.data?.rows || []
  const topCountriesTotals = batchResponse?.data?.items?.top_countries?.data?.totals
  const topOsData = batchResponse?.data?.items?.top_operating_systems?.data?.rows || []
  const topOsTotals = batchResponse?.data?.items?.top_operating_systems?.data?.totals
  const topDevicesData = batchResponse?.data?.items?.top_device_categories?.data?.rows || []
  const topDevicesTotals = batchResponse?.data?.items?.top_device_categories?.data?.totals
  const topSourceCategoriesData = batchResponse?.data?.items?.top_source_categories?.data?.rows || []
  const topSourceCategoriesTotals = batchResponse?.data?.items?.top_source_categories?.data?.totals
  const topSearchEnginesData = batchResponse?.data?.items?.top_search_engines?.data?.rows || []
  const topSearchEnginesTotals = batchResponse?.data?.items?.top_search_engines?.data?.totals
  const topSocialMediaData = batchResponse?.data?.items?.top_social_media?.data?.rows || []
  const topSocialMediaTotals = batchResponse?.data?.items?.top_social_media?.data?.totals
  const topEntryPagesData = batchResponse?.data?.items?.top_entry_pages?.data?.rows || []
  const topEntryPagesTotals = batchResponse?.data?.items?.top_entry_pages?.data?.totals

  // Transform chart data
  const chartData = useMemo(() => {
    if (!trafficTrendsResponse?.labels || !trafficTrendsResponse?.datasets) return []

    const labels = trafficTrendsResponse.labels
    const datasets = trafficTrendsResponse.datasets

    const currentDatasets = datasets.filter((d) => !d.comparison)
    const previousDatasets = datasets.filter((d) => d.comparison)

    return labels.map((label, index) => {
      const point: Record<string, string | number> = { date: label }

      currentDatasets.forEach((dataset) => {
        point[dataset.key] = Number(dataset.data[index]) || 0
      })

      previousDatasets.forEach((dataset) => {
        const baseKey = dataset.key.replace('_previous', '')
        point[`${baseKey}Previous`] = Number(dataset.data[index]) || 0
      })

      return point
    })
  }, [trafficTrendsResponse])

  const chartTotals = useMemo(() => {
    if (!trafficTrendsResponse?.datasets) {
      return { visitors: 0, visitorsPrevious: 0, views: 0, viewsPrevious: 0 }
    }

    const datasets = trafficTrendsResponse.datasets
    const visitorsDataset = datasets.find((d) => d.key === 'visitors' && !d.comparison)
    const visitorsPrevDataset = datasets.find((d) => d.key === 'visitors_previous' && d.comparison)
    const viewsDataset = datasets.find((d) => d.key === 'views' && !d.comparison)
    const viewsPrevDataset = datasets.find((d) => d.key === 'views_previous' && d.comparison)

    return {
      visitors: visitorsDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      visitorsPrevious: visitorsPrevDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      views: viewsDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      viewsPrevious: viewsPrevDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
    }
  }, [trafficTrendsResponse])

  const trafficTrendsMetrics = [
    {
      key: 'visitors',
      label: __('Visitors', 'wp-statistics'),
      color: 'var(--chart-1)',
      enabled: true,
      value:
        chartTotals.visitors >= 1000
          ? `${formatDecimal(chartTotals.visitors / 1000)}k`
          : formatDecimal(chartTotals.visitors),
      previousValue:
        chartTotals.visitorsPrevious >= 1000
          ? `${formatDecimal(chartTotals.visitorsPrevious / 1000)}k`
          : formatDecimal(chartTotals.visitorsPrevious),
    },
    {
      key: 'views',
      label: __('Views', 'wp-statistics'),
      color: 'var(--chart-2)',
      enabled: true,
      value:
        chartTotals.views >= 1000 ? `${formatDecimal(chartTotals.views / 1000)}k` : formatDecimal(chartTotals.views),
      previousValue:
        chartTotals.viewsPrevious >= 1000
          ? `${formatDecimal(chartTotals.viewsPrevious / 1000)}k`
          : formatDecimal(chartTotals.viewsPrevious),
    },
  ]

  const calcPercentage = usePercentageCalc()

  // Build overview metrics
  const overviewMetrics = useMemo(() => {
    const totals = metricsResponse?.totals

    const visitors = Number(totals?.visitors?.current) || 0
    const prevVisitors = Number(totals?.visitors?.previous) || 0

    const topReferrer = metricsTopReferrer?.items?.[0]?.referrer_name
    const topCountry = metricsTopCountry?.items?.[0]?.country_name
    const topBrowser = metricsTopBrowser?.items?.[0]?.browser_name
    const topSearchEngine = metricsTopSearchEngine?.items?.[0]?.referrer_name
    const topSocial = metricsTopSocial?.items?.[0]?.referrer_name
    const topEntryPage = metricsTopEntryPage?.items?.[0]?.page_title

    return [
      // Row 1
      {
        label: __('Referred Visitors', 'wp-statistics'),
        value: formatCompactNumber(visitors),
        ...calcPercentage(visitors, prevVisitors),
        tooltipContent: __('Total visitors from external sources', 'wp-statistics'),
      },
      {
        label: __('Top Referrer', 'wp-statistics'),
        value: topReferrer || '-',
        tooltipContent: __('Most common traffic source', 'wp-statistics'),
      },
      {
        label: __('Top Country', 'wp-statistics'),
        value: topCountry || '-',
        tooltipContent: __('Country with most referred visitors', 'wp-statistics'),
      },
      {
        label: __('Top Browser', 'wp-statistics'),
        value: topBrowser || '-',
        tooltipContent: __('Most used browser by referred visitors', 'wp-statistics'),
      },
      // Row 2
      {
        label: __('Top Search Engine', 'wp-statistics'),
        value: topSearchEngine || '-',
        tooltipContent: __('Search engine driving most traffic', 'wp-statistics'),
      },
      {
        label: __('Top Social Media', 'wp-statistics'),
        value: topSocial || '-',
        tooltipContent: __('Social platform driving most traffic', 'wp-statistics'),
      },
      {
        label: __('Top Entry Page', 'wp-statistics'),
        value: topEntryPage || '-',
        tooltipContent: __('Most visited landing page from referrals', 'wp-statistics'),
      },
    ]
  }, [metricsResponse, metricsTopReferrer, metricsTopCountry, metricsTopBrowser, metricsTopSearchEngine, metricsTopSocial, metricsTopEntryPage, calcPercentage])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Referrals Overview', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && isInitialized && (
            <FilterButton fields={filterFields} appliedFilters={appliedFilters || []} onApplyFilters={handleApplyFilters} filterGroup="referrals" />
          )}
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
      </div>

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="referrals-overview" />
        {appliedFilters && appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} className="mb-2" />
        )}

        {showSkeleton || showFullPageLoading ? (
          <div className="grid gap-3 grid-cols-12">
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={7} columns={4} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12">
              <PanelSkeleton titleWidth="w-32">
                <ChartSkeleton height={256} showTitle={false} />
              </PanelSkeleton>
            </div>
            {[1, 2].map((i) => (
              <div key={i} className="col-span-6">
                <PanelSkeleton>
                  <BarListSkeleton items={5} />
                </PanelSkeleton>
              </div>
            ))}
            {[1, 2, 3].map((i) => (
              <div key={i} className="col-span-4">
                <PanelSkeleton>
                  <BarListSkeleton items={5} showIcon />
                </PanelSkeleton>
              </div>
            ))}
            {[1, 2, 3].map((i) => (
              <div key={i} className="col-span-4">
                <PanelSkeleton>
                  <BarListSkeleton items={5} />
                </PanelSkeleton>
              </div>
            ))}
          </div>
        ) : (
          <div className="grid gap-3 grid-cols-12">
            {/* Row 1: Overview Metrics */}
            <div className="col-span-12">
              <Panel>
                <Metrics metrics={overviewMetrics} columns={4} />
              </Panel>
            </div>

            {/* Row 2: Traffic Trends */}
            <div className="col-span-12">
              <LineChart
                title={__('Traffic Trends', 'wp-statistics')}
                data={chartData}
                metrics={trafficTrendsMetrics}
                showPreviousPeriod={true}
                timeframe={timeframe}
                onTimeframeChange={handleTimeframeChange}
                loading={isChartRefetching}
              />
            </div>

            {/* Row 3: Top Entry Pages + Top Referrers */}
            <div className="col-span-12 lg:col-span-6">
              <HorizontalBarList
                title={__('Top Entry Pages', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topEntryPagesTotals?.visitors?.current ?? topEntryPagesTotals?.visitors) || 1
                  return topEntryPagesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)

                    return {
                      label: item.page_title || item.page_uri || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.page_title || item.page_uri || '',
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Entry Pages', 'wp-statistics'),
                  action: () => console.log('View entry pages'),
                }}
              />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <HorizontalBarList
                title={__('Top Referrers', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topReferrersTotals?.visitors?.current ?? topReferrersTotals?.visitors) || 1
                  return topReferrersData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const displayName = item.referrer_name || item.referrer_domain || __('Direct', 'wp-statistics')

                    return {
                      label: displayName,
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: displayName,
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Referrers', 'wp-statistics'),
                  action: () => console.log('View referrers'),
                }}
              />
            </div>

            {/* Row 4: Countries, OS, Device Type */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Countries', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topCountriesTotals?.visitors?.current ?? topCountriesTotals?.visitors) || 1
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
                  action: () => console.log('View countries'),
                }}
              />
            </div>
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Operating Systems', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topOsTotals?.visitors?.current ?? topOsTotals?.visitors) || 1
                  return topOsData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
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
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.os_name || '',
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Operating Systems', 'wp-statistics'),
                  action: () => console.log('View OS'),
                }}
              />
            </div>
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Device Categories', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topDevicesTotals?.visitors?.current ?? topDevicesTotals?.visitors) || 1
                  return topDevicesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
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
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.device_type_name || '',
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Device Types', 'wp-statistics'),
                  action: () => console.log('View devices'),
                }}
              />
            </div>

            {/* Row 5: Source Categories, Search Engines, Social Media */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Source Categories', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topSourceCategoriesTotals?.visitors?.current ?? topSourceCategoriesTotals?.visitors) || 1
                  return topSourceCategoriesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)

                    return {
                      label: item.referrer_channel || __('Unknown', 'wp-statistics'),
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: item.referrer_channel || '',
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Source Categories', 'wp-statistics'),
                  action: () => console.log('View source categories'),
                }}
              />
            </div>
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Search Engines', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topSearchEnginesTotals?.visitors?.current ?? topSearchEnginesTotals?.visitors) || 1
                  return topSearchEnginesData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const displayName = item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics')

                    return {
                      label: displayName,
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: displayName,
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Search Engines', 'wp-statistics'),
                  action: () => console.log('View search engines'),
                }}
              />
            </div>
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Social Media', 'wp-statistics')}
                items={(() => {
                  const totalVisitors = Number(topSocialMediaTotals?.visitors?.current ?? topSocialMediaTotals?.visitors) || 1
                  return topSocialMediaData.map((item) => {
                    const currentValue = Number(item.visitors) || 0
                    const previousValue = Number(item.previous?.visitors) || 0
                    const { percentage, isNegative } = calcPercentage(currentValue, previousValue)
                    const displayName = item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics')

                    return {
                      label: displayName,
                      value: currentValue,
                      percentage,
                      fillPercentage: calcSharePercentage(currentValue, totalVisitors),
                      isNegative,
                      tooltipTitle: displayName,
                      tooltipSubtitle: `${__('Previous: ', 'wp-statistics')} ${previousValue.toLocaleString()}`,
                    }
                  })
                })()}
                link={{
                  title: __('View Social Media', 'wp-statistics'),
                  action: () => console.log('View social media'),
                }}
              />
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
