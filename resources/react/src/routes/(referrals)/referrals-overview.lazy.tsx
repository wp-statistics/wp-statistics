import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { Metrics } from '@/components/custom/metrics'
import { NoticeContainer } from '@/components/ui/notice-container'
import { Panel } from '@/components/ui/panel'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useChartData } from '@/hooks/use-chart-data'
import { useComparisonDateLabel } from '@/hooks/use-comparison-date-label'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { formatCompactNumber, formatDecimal } from '@/lib/utils'
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
    isCompareEnabled,
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

  // Transform chart data using shared hook
  const { data: chartData, metrics: trafficTrendsMetrics } = useChartData(trafficTrendsResponse, {
    metrics: [
      { key: 'visitors', label: __('Visitors', 'wp-statistics'), color: 'var(--chart-1)' },
      { key: 'views', label: __('Views', 'wp-statistics'), color: 'var(--chart-2)' },
    ],
    showPreviousValues: isCompareEnabled,
    preserveNull: true,
  })

  const calcPercentage = usePercentageCalc()
  const { label: comparisonDateLabel } = useComparisonDateLabel()

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
        ...(isCompareEnabled ? calcPercentage(visitors, prevVisitors) : {}),
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
  }, [
    metricsResponse,
    metricsTopReferrer,
    metricsTopCountry,
    metricsTopBrowser,
    metricsTopSearchEngine,
    metricsTopSocial,
    metricsTopEntryPage,
    calcPercentage,
    isCompareEnabled,
  ])

  return (
    <div className="min-w-0">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3 ">
        <h1 className="text-2xl font-semibold text-neutral-800">{__('Referrals Overview', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          <div className="hidden lg:flex">
            {filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={appliedFilters || []}
                onApplyFilters={handleApplyFilters}
                filterGroup="referrals"
              />
            )}
          </div>
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
                showPreviousPeriod={isCompareEnabled}
                timeframe={timeframe}
                onTimeframeChange={handleTimeframeChange}
                loading={isChartRefetching}
                compareDateTo={apiDateParams.previous_date_to}
                dateTo={apiDateParams.date_to}
              />
            </div>

            {/* Row 3: Top Entry Pages + Top Referrers */}
            <div className="col-span-12 lg:col-span-6">
              <HorizontalBarList
                title={__('Top Entry Pages', 'wp-statistics')}
                showComparison={isCompareEnabled}
                columnHeaders={{
                  left: __('Page', 'wp-statistics'),
                  right: __('Visitors', 'wp-statistics'),
                }}
                items={transformToBarList(topEntryPagesData, {
                  label: (item) => item.page_title || item.page_uri || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topEntryPagesTotals?.visitors?.current ?? topEntryPagesTotals?.visitors) || 1,
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
                link={{
                  action: () => console.log('View entry pages'),
                }}
              />
            </div>
            <div className="col-span-12 lg:col-span-6">
              <HorizontalBarList
                title={__('Top Referrers', 'wp-statistics')}
                showComparison={isCompareEnabled}
                columnHeaders={{
                  left: __('Referrer', 'wp-statistics'),
                  right: __('Visitors', 'wp-statistics'),
                }}
                items={transformToBarList(topReferrersData, {
                  label: (item) => item.referrer_name || item.referrer_domain || __('Direct', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topReferrersTotals?.visitors?.current ?? topReferrersTotals?.visitors) || 1,
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
                link={{
                  action: () => console.log('View referrers'),
                }}
              />
            </div>

            {/* Row 4: Countries, OS, Device Type */}
            <div className="col-span-12 lg:col-span-4">
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
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/flags/${item.country_code?.toLowerCase() || '000'}.svg`}
                      alt={item.country_name || ''}
                      className="w-4 h-3"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
                link={{
                  action: () => console.log('View countries'),
                }}
              />
            </div>
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Operating Systems', 'wp-statistics')}
                showComparison={isCompareEnabled}
                columnHeaders={{
                  left: __('OS', 'wp-statistics'),
                  right: __('Visitors', 'wp-statistics'),
                }}
                items={transformToBarList(topOsData, {
                  label: (item) => item.os_name || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topOsTotals?.visitors?.current ?? topOsTotals?.visitors) || 1,
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/operating-system/${(item.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_')}.svg`}
                      alt={item.os_name || ''}
                      className="w-4 h-3"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
                link={{
                  action: () => console.log('View OS'),
                }}
              />
            </div>
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Device Categories', 'wp-statistics')}
                showComparison={isCompareEnabled}
                columnHeaders={{
                  left: __('Device', 'wp-statistics'),
                  right: __('Visitors', 'wp-statistics'),
                }}
                items={transformToBarList(topDevicesData, {
                  label: (item) => item.device_type_name || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topDevicesTotals?.visitors?.current ?? topDevicesTotals?.visitors) || 1,
                  icon: (item) => (
                    <img
                      src={`${pluginUrl}public/images/device/${(item.device_type_name || 'desktop').toLowerCase()}.svg`}
                      alt={item.device_type_name || ''}
                      className="w-4 h-3"
                    />
                  ),
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
                link={{
                  action: () => console.log('View devices'),
                }}
              />
            </div>

            {/* Row 5: Source Categories, Search Engines, Social Media */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Source Categories', 'wp-statistics')}
                showComparison={isCompareEnabled}
                columnHeaders={{
                  left: __('Source', 'wp-statistics'),
                  right: __('Visitors', 'wp-statistics'),
                }}
                items={transformToBarList(topSourceCategoriesData, {
                  label: (item) => item.referrer_channel || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topSourceCategoriesTotals?.visitors?.current ?? topSourceCategoriesTotals?.visitors) || 1,
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
                link={{
                  action: () => console.log('View source categories'),
                }}
              />
            </div>
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Search Engines', 'wp-statistics')}
                showComparison={isCompareEnabled}
                columnHeaders={{
                  left: __('Search Engine', 'wp-statistics'),
                  right: __('Visitors', 'wp-statistics'),
                }}
                items={transformToBarList(topSearchEnginesData, {
                  label: (item) => item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topSearchEnginesTotals?.visitors?.current ?? topSearchEnginesTotals?.visitors) || 1,
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
                link={{
                  action: () => console.log('View search engines'),
                }}
              />
            </div>
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Social Media', 'wp-statistics')}
                showComparison={isCompareEnabled}
                columnHeaders={{
                  left: __('Platform', 'wp-statistics'),
                  right: __('Visitors', 'wp-statistics'),
                }}
                items={transformToBarList(topSocialMediaData, {
                  label: (item) => item.referrer_name || item.referrer_domain || __('Unknown', 'wp-statistics'),
                  value: (item) => Number(item.visitors) || 0,
                  previousValue: (item) => Number(item.previous?.visitors) || 0,
                  total: Number(topSocialMediaTotals?.visitors?.current ?? topSocialMediaTotals?.visitors) || 1,
                  isCompareEnabled,
                  comparisonDateLabel,
                })}
                link={{
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
