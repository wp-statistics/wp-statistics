import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, Link } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { type DateRange, DateRangePicker } from '@/components/custom/date-range-picker'
import { ErrorMessage } from '@/components/custom/error-message'
import { FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { LineChart } from '@/components/custom/line-chart'
import { type MetricItem, Metrics } from '@/components/custom/metrics'
import { Panel } from '@/components/ui/panel'
import { NoticeContainer } from '@/components/ui/notice-container'
import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { calcSharePercentage, decodeText, formatCompactNumber, formatDecimal, formatDuration } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { getPageInsightsOverviewQueryOptions } from '@/services/page-insight/get-page-insights-overview'

export const Route = createLazyFileRoute('/(page-insights)/page-insights-overview')({
  component: RouteComponent,
  errorComponent: ({ error }) => (
    <div className="p-6 text-center">
      <h2 className="text-xl font-semibold text-destructive mb-2">Error Loading Page</h2>
      <p className="text-muted-foreground">{error.message}</p>
    </div>
  ),
})

function RouteComponent() {
  // Use global filters context for date range and filters
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    filters: appliedFilters,
    setDateRange,
    applyFilters: handleApplyFilters,
    removeFilter: handleRemoveFilter,
    isInitialized,
    apiDateParams,
  } = useGlobalFilters()

  const wp = WordPress.getInstance()

  // Get filter fields for 'views' group from localized data
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('views') as FilterField[]
  }, [wp])

  const [timeframe, setTimeframe] = useState<'daily' | 'weekly' | 'monthly'>('daily')

  // Track if only timeframe changed (for loading behavior)
  const [isTimeframeOnlyChange, setIsTimeframeOnlyChange] = useState(false)
  const prevFiltersRef = useRef<string>(JSON.stringify(appliedFilters))
  const prevDateFromRef = useRef<Date | undefined>(dateFrom)
  const prevDateToRef = useRef<Date | undefined>(dateTo)

  // Detect what changed when data is being fetched
  useEffect(() => {
    const currentFilters = JSON.stringify(appliedFilters)

    const filtersChanged = currentFilters !== prevFiltersRef.current
    const dateRangeChanged = dateFrom !== prevDateFromRef.current || dateTo !== prevDateToRef.current

    // If filters or dates changed, it's NOT a timeframe-only change
    if (filtersChanged || dateRangeChanged) {
      setIsTimeframeOnlyChange(false)
    }

    // Update refs
    prevFiltersRef.current = currentFilters
    prevDateFromRef.current = dateFrom
    prevDateToRef.current = dateTo
  }, [appliedFilters, dateFrom, dateTo])

  // Custom timeframe setter that tracks the change type
  const handleTimeframeChange = useCallback((newTimeframe: 'daily' | 'weekly' | 'monthly') => {
    setIsTimeframeOnlyChange(true)
    setTimeframe(newTimeframe)
  }, [])

  // Handle date range updates from DateRangePicker
  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange }) => {
      setDateRange(values.range, values.rangeCompare)
    },
    [setDateRange]
  )

  // Batch query for all overview data
  const {
    data: batchResponse,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getPageInsightsOverviewQueryOptions({
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

  // Only show skeleton on initial load
  const showSkeleton = isLoading && !batchResponse
  // Show full page loading when filters/dates change
  const showFullPageLoading = isFetching && !isLoading && !isTimeframeOnlyChange
  // Show loading indicator on chart only when timeframe changes
  const isChartRefetching = isFetching && !isLoading && isTimeframeOnlyChange

  // Extract data from batch response
  const metricsResponse = batchResponse?.data?.items?.metrics
  const metricsTopPostType = batchResponse?.data?.items?.metrics_top_post_type
  const pageViewsTrendsResponse = batchResponse?.data?.items?.page_views_trends
  const topPagesData = batchResponse?.data?.items?.top_pages?.data?.rows || []
  const topPagesTotals = batchResponse?.data?.items?.top_pages?.data?.totals
  const entryPagesData = batchResponse?.data?.items?.entry_pages?.data?.rows || []
  const entryPagesTotals = batchResponse?.data?.items?.entry_pages?.data?.totals
  const pages404Data = batchResponse?.data?.items?.pages_404?.data?.rows || []

  const calcPercentage = usePercentageCalc()

  // Transform metrics to component format
  const metricsData = useMemo<MetricItem[]>(() => {
    const totals = metricsResponse?.totals
    if (!totals) return []

    return [
      {
        label: __('Views', 'wp-statistics'),
        value: formatCompactNumber(Number(totals.views?.current) || 0),
        previousValue: formatCompactNumber(Number(totals.views?.previous) || 0),
        percentage: calcPercentage(Number(totals.views?.current), Number(totals.views?.previous)),
      },
      {
        label: __('Visitors', 'wp-statistics'),
        value: formatCompactNumber(Number(totals.visitors?.current) || 0),
        previousValue: formatCompactNumber(Number(totals.visitors?.previous) || 0),
        percentage: calcPercentage(Number(totals.visitors?.current), Number(totals.visitors?.previous)),
      },
      {
        label: __('Avg. Time on Page', 'wp-statistics'),
        value: formatDuration(Number(totals.avg_time_on_page?.current) || 0),
        previousValue: formatDuration(Number(totals.avg_time_on_page?.previous) || 0),
        percentage: calcPercentage(Number(totals.avg_time_on_page?.current), Number(totals.avg_time_on_page?.previous)),
      },
      {
        label: __('Bounce Rate', 'wp-statistics'),
        value: `${formatDecimal(Number(totals.bounce_rate?.current) || 0)}%`,
        previousValue: `${formatDecimal(Number(totals.bounce_rate?.previous) || 0)}%`,
        percentage: calcPercentage(Number(totals.bounce_rate?.current), Number(totals.bounce_rate?.previous)),
        invertTrend: true,
      },
      {
        label: __('Pages/Session', 'wp-statistics'),
        value: formatDecimal(Number(totals.pages_per_session?.current) || 0),
        previousValue: formatDecimal(Number(totals.pages_per_session?.previous) || 0),
        percentage: calcPercentage(Number(totals.pages_per_session?.current), Number(totals.pages_per_session?.previous)),
      },
      {
        label: __('Top Content Type', 'wp-statistics'),
        value: metricsTopPostType?.items?.[0]?.page_type || '-',
        previousValue: undefined,
        percentage: undefined,
      },
    ]
  }, [metricsResponse, metricsTopPostType, calcPercentage])

  // Transform page views trends chart data
  const pageViewsTrendsData = useMemo(() => {
    if (!pageViewsTrendsResponse?.labels || !pageViewsTrendsResponse?.datasets) return []

    const labels = pageViewsTrendsResponse.labels
    const datasets = pageViewsTrendsResponse.datasets

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
  }, [pageViewsTrendsResponse])

  // Calculate totals for chart metrics
  const chartTotals = useMemo(() => {
    if (!pageViewsTrendsResponse?.datasets) {
      return { views: 0, viewsPrevious: 0, visitors: 0, visitorsPrevious: 0 }
    }

    const datasets = pageViewsTrendsResponse.datasets
    const viewsDataset = datasets.find((d) => d.key === 'views' && !d.comparison)
    const viewsPrevDataset = datasets.find((d) => d.key === 'views_previous' && d.comparison)
    const visitorsDataset = datasets.find((d) => d.key === 'visitors' && !d.comparison)
    const visitorsPrevDataset = datasets.find((d) => d.key === 'visitors_previous' && d.comparison)

    return {
      views: viewsDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      viewsPrevious: viewsPrevDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      visitors: visitorsDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
      visitorsPrevious: visitorsPrevDataset?.data?.reduce((sum, v) => sum + Number(v), 0) || 0,
    }
  }, [pageViewsTrendsResponse])

  // Define chart metrics
  const chartMetrics = useMemo(() => {
    return [
      {
        key: 'views',
        label: __('Views', 'wp-statistics'),
        color: 'var(--chart-1)',
        enabled: true,
        value: chartTotals.views >= 1000 ? `${formatDecimal(chartTotals.views / 1000)}k` : formatDecimal(chartTotals.views),
        previousValue:
          chartTotals.viewsPrevious >= 1000
            ? `${formatDecimal(chartTotals.viewsPrevious / 1000)}k`
            : formatDecimal(chartTotals.viewsPrevious),
      },
      {
        key: 'visitors',
        label: __('Visitors', 'wp-statistics'),
        color: 'var(--chart-2)',
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
    ]
  }, [chartTotals])

  // Transform top pages data
  const topPagesListData = useMemo(() => {
    const totalViews = Number(topPagesTotals?.views) || topPagesData.reduce((sum, row) => sum + Number(row.views), 0)

    return topPagesData.map((row) => ({
      label: decodeText(row.page_title) || row.page_uri,
      value: formatCompactNumber(Number(row.views)),
      previousValue: row.previous ? formatCompactNumber(Number(row.previous.views)) : undefined,
      percentage: calcPercentage(Number(row.views), row.previous ? Number(row.previous.views) : undefined),
      fillPercentage: calcSharePercentage(Number(row.views), totalViews),
      link: row.page_uri,
    }))
  }, [topPagesData, topPagesTotals, calcPercentage])

  // Transform entry pages data
  const entryPagesListData = useMemo(() => {
    const totalSessions =
      Number(entryPagesTotals?.sessions) || entryPagesData.reduce((sum, row) => sum + Number(row.sessions), 0)

    return entryPagesData.map((row) => ({
      label: decodeText(row.page_title) || row.page_uri,
      value: formatCompactNumber(Number(row.sessions)),
      previousValue: row.previous ? formatCompactNumber(Number(row.previous.sessions)) : undefined,
      percentage: calcPercentage(Number(row.sessions), row.previous ? Number(row.previous.sessions) : undefined),
      fillPercentage: calcSharePercentage(Number(row.sessions), totalSessions),
      link: row.page_uri,
    }))
  }, [entryPagesData, entryPagesTotals, calcPercentage])

  // Transform 404 pages data
  const pages404ListData = useMemo(() => {
    const totalViews = pages404Data.reduce((sum, row) => sum + Number(row.views), 0)

    return pages404Data.map((row) => ({
      label: row.page_uri,
      value: formatCompactNumber(Number(row.views)),
      previousValue: row.previous ? formatCompactNumber(Number(row.previous.views)) : undefined,
      percentage: calcPercentage(Number(row.views), row.previous ? Number(row.previous.views) : undefined),
      fillPercentage: calcSharePercentage(Number(row.views), totalViews),
    }))
  }, [pages404Data, calcPercentage])

  return (
    <div className="min-w-0">
      {/* Header row */}
      <div className="flex items-center justify-between px-4 py-3 bg-white border-b border-input">
        <h1 className="text-xl font-semibold text-neutral-800">{__('Page Insights', 'wp-statistics')}</h1>
        <div className="flex items-center gap-3">
          {filterFields.length > 0 && isInitialized && (
            <FilterButton
              fields={filterFields}
              appliedFilters={appliedFilters || []}
              onApplyFilters={handleApplyFilters}
            />
          )}
          <DateRangePicker
            initialDateFrom={dateFrom}
            initialDateTo={dateTo}
            initialCompareFrom={compareDateFrom}
            initialCompareTo={compareDateTo}
            showCompare={true}
            onUpdate={handleDateRangeUpdate}
            align="end"
          />
        </div>
      </div>

      <div className="p-2">
        <NoticeContainer className="mb-2" currentRoute="page-insights-overview" />

        {/* Applied filters row */}
        {appliedFilters && appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} className="mb-2" />
        )}

        {isError ? (
          <div className="p-4 text-center">
            <ErrorMessage message={__('Failed to load page insights', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground mt-2">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <div className="grid gap-2 grid-cols-12">
            {/* Metrics skeleton */}
            <div className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={6} columns={6} />
              </PanelSkeleton>
            </div>

            {/* Chart skeleton */}
            <div className="col-span-12">
              <PanelSkeleton titleWidth="w-36">
                <ChartSkeleton height={280} showTitle={false} />
              </PanelSkeleton>
            </div>

            {/* Bar lists skeleton */}
            <div className="col-span-12 lg:col-span-4">
              <PanelSkeleton titleWidth="w-24">
                <BarListSkeleton rows={5} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-4">
              <PanelSkeleton titleWidth="w-28">
                <BarListSkeleton rows={5} />
              </PanelSkeleton>
            </div>
            <div className="col-span-12 lg:col-span-4">
              <PanelSkeleton titleWidth="w-24">
                <BarListSkeleton rows={5} />
              </PanelSkeleton>
            </div>
          </div>
        ) : (
          <div className={`grid gap-2 grid-cols-12 ${showFullPageLoading ? 'opacity-60 pointer-events-none' : ''}`}>
            {/* Metrics Panel */}
            <div className="col-span-12">
              <Panel>
                <Metrics items={metricsData} columns={6} />
              </Panel>
            </div>

            {/* Page Views Trends Chart */}
            <div className="col-span-12">
              <LineChart
                title={__('Page Views Trends', 'wp-statistics')}
                data={pageViewsTrendsData}
                metrics={chartMetrics}
                showPreviousPeriod={!!(compareDateFrom && compareDateTo)}
                timeframe={timeframe}
                onTimeframeChange={handleTimeframeChange}
                loading={isChartRefetching}
              />
            </div>

            {/* Top Pages */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Top Pages', 'wp-statistics')}
                data={topPagesListData}
                valueLabel={__('Views', 'wp-statistics')}
                emptyStateMessage={__('No page data available', 'wp-statistics')}
                viewMoreLink="/top-pages"
                viewMoreLabel={__('View All Pages', 'wp-statistics')}
              />
            </div>

            {/* Entry Pages */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('Entry Pages', 'wp-statistics')}
                data={entryPagesListData}
                valueLabel={__('Sessions', 'wp-statistics')}
                emptyStateMessage={__('No entry page data available', 'wp-statistics')}
                viewMoreLink="/entry-pages"
                viewMoreLabel={__('View All Entry Pages', 'wp-statistics')}
              />
            </div>

            {/* 404 Pages */}
            <div className="col-span-12 lg:col-span-4">
              <HorizontalBarList
                title={__('404 Pages', 'wp-statistics')}
                data={pages404ListData}
                valueLabel={__('Views', 'wp-statistics')}
                emptyStateMessage={__('No 404 errors found', 'wp-statistics')}
                viewMoreLink="/404-pages"
                viewMoreLabel={__('View All 404 Pages', 'wp-statistics')}
              />
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
